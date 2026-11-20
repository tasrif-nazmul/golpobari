<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PageService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $payments = Payment::with('order:id,order_number,customer_name,customer_phone,final_price')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($w) => $w->where('transaction_id', 'like', "%{$v}%")->orWhere('sender_number', 'like', "%{$v}%")
                ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$v}%"))))
            ->orderBy($status === 'pending' ? 'created_at' : 'updated_at', $status === 'pending' ? 'asc' : 'desc')
            ->paginate(25)->withQueryString();

        $dupes = Payment::whereIn('transaction_id', $payments->pluck('transaction_id'))
            ->select('transaction_id')->groupBy('transaction_id')->havingRaw('count(*) > 1')->pluck('transaction_id')->all();

        return view('admin.payments.index', compact('payments', 'status', 'dupes'));
    }

    public function verify(Request $request, Payment $payment)
    {
        try {
            $this->payments->verify($payment, $request->input('note'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // One tap from "money received" to the page editor.
        if ($request->boolean('make_page')) {
            $order = $payment->order()->first()?->load(['package', 'page']);
            if ($order) {
                try {
                    $page = $order->page ?? app(PageService::class)->createFromOrder($order);
                } catch (RuntimeException $e) {
                    return redirect()->route('admin.orders.show', $order)->with('error', 'Payment verified, but the page could not be created: '.$e->getMessage());
                }

                return redirect()->route('admin.pages.edit', $page)->with('success', 'Payment verified and a page draft is ready. Check it, then Publish.');
            }
        }

        return back()->with('success', 'Payment verified. The order is ready for page creation.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:200']]);
        try {
            $this->payments->reject($payment, $data['reason']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment rejected. The customer can re-submit from the tracking page.');
    }

    public function refund(Request $request, Payment $payment)
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:200']]);
        try {
            $this->payments->refund($payment, $data['note'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment marked as refunded. Send the money back via bKash manually.');
    }
}
