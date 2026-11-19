<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\PageMedia;
use App\Models\Template;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Support\ActivityLogger;
use App\Support\ContentSchema;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use ZipArchive;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        $sortable = ['created_at', 'final_price', 'order_number', 'customer_name', 'status'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $orders = Order::query()
            ->with(['payment', 'page:id,order_id,slug,status', 'template:id,name'])
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('payment'), fn ($q, $v) => $q->where('payment_status', $v))
            ->when($request->query('category'), fn ($q, $v) => $q->where('category_id', (int) $v))
            ->when($request->query('package'), fn ($q, $v) => $q->where('package_id', (int) $v))
            ->when($request->query('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->query('to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy($sort, $dir)->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'categories' => Category::orderBy('sort_order')->pluck('name', 'id'),
            'packages' => Package::orderBy('sort_order')->pluck('name', 'id'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['payments.verifier', 'histories.admin', 'media', 'page', 'template.fields', 'category', 'package', 'coupon']);

        return view('admin.orders.show', [
            'order' => $order,
            'packages' => Package::orderBy('sort_order')->get(),
            'templates' => Template::with('category')->orderBy('sort_order')->get(),
            'duplicates' => $order->payments->mapWithKeys(fn ($p) => [$p->id => $p->duplicates()->with('order:id,order_number')->get()]),
        ]);
    }

    /** Customer info, package, template, manual discount. */
    public function update(Request $request, Order $order)
    {
        $request->merge(['customer_phone' => Phone::normalize($request->input('customer_phone'))]);
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'customer_phone' => ['required', 'regex:'.Phone::BD_MOBILE],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'package_id' => ['required', 'exists:packages,id'],
            'template_id' => ['required', 'exists:templates,id'],
            'manual_discount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'desired_slug' => ['nullable', 'string', 'max:60'],
        ]);

        $template = Template::findOrFail($data['template_id']);
        $order->fill([
            'customer_name' => ContentSchema::clean($data['customer_name']),
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'template_id' => $template->id,
            'category_id' => $template->category_id,
            'desired_slug' => $data['desired_slug'] ?? null,
        ]);
        $snapshot = $order->snapshot ?? [];
        $snapshot['template'] = $template->name;
        $order->snapshot = $snapshot;
        $order->save();

        $this->orders->reprice($order, Package::findOrFail($data['package_id']), (float) ($data['manual_discount'] ?? 0));
        if ($order->page && $order->page->template_id !== $template->id) {
            $order->page->update(['template_id' => $template->id]);
        }
        ActivityLogger::log('order.updated', $order, $order->order_number, ['final_price' => (string) $order->final_price]);

        return back()->with('success', 'Order updated.');
    }

    public function updateContent(Request $request, Order $order)
    {
        $order->load('template.fields');
        $schema = new ContentSchema($order->template->fields);
        $request->validate($schema->rules('content', false), [], $schema->attributes('content'));
        $order->update(['content' => $schema->sanitize($request->input('content', []))]);
        ActivityLogger::log('order.content_edited', $order, $order->order_number);

        return back()->with('success', 'Customer content saved.');
    }

    public function status(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'note' => ['nullable', 'string', 'max:250'],
        ]);
        $to = OrderStatus::from($data['status']);

        if ($to === OrderStatus::Refunded && $order->payment_status !== PaymentStatus::Refunded) {
            return back()->with('error', 'Use "Mark refunded" on the payment so the payment and order stay consistent.');
        }
        if ($to === OrderStatus::Cancelled) {
            $this->orders->releaseCoupon($order);
        }

        $this->orders->transition($order, $to, $data['note'] ?? 'Changed by admin', true);

        return back()->with('success', 'Status changed to '.$to->label().'.');
    }

    public function notes(Request $request, Order $order)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:5000']]);
        $order->update($data);

        return back()->with('success', 'Notes saved.');
    }

    public function addMedia(Request $request, Order $order, ImageService $images)
    {
        $request->validate([
            'field_key' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'max:12288', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp'],
        ]);
        try {
            foreach ($request->file('files') as $file) {
                $images->storeForOrder($file, $order, $request->input('field_key'), $order->page?->id);
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Photos added.');
    }

    public function downloadMedia(Order $order, PageMedia $media)
    {
        abort_unless($media->order_id === $order->id, 404);
        $disk = Storage::disk('uploads');
        abort_unless($disk->exists($media->path), 404);
        $name = $order->order_number.'-'.$media->field_key.'-'.$media->id.'.webp';

        return response()->download($disk->path($media->path), $name);
    }

    public function downloadAll(Order $order)
    {
        abort_unless(class_exists(ZipArchive::class), 501, 'The PHP zip extension is not installed.');
        $disk = Storage::disk('uploads');
        $tmp = tempnam(sys_get_temp_dir(), 'gbzip');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        foreach ($order->media as $m) {
            if ($disk->exists($m->path)) {
                $zip->addFile($disk->path($m->path), $m->field_key.'/'.str_pad((string) $m->sort_order, 2, '0', STR_PAD_LEFT).'-'.$m->id.'.webp');
            }
        }
        $zip->close();

        return response()->download($tmp, $order->order_number.'-photos.zip')->deleteFileAfterSend();
    }

    public function deleteMedia(Order $order, PageMedia $media, ImageService $images)
    {
        abort_unless($media->order_id === $order->id, 404);
        $images->delete($media);
        ActivityLogger::log('media.deleted', $order, $order->order_number.' · '.$media->field_key);

        return back()->with('success', 'Photo removed.');
    }
}
