<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class CouponController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $coupons = Coupon::withSum('usages', 'discount_amount')
            ->when($request->query('q'), fn ($q, $v) => $q->where('code', 'like', '%'.strtoupper($v).'%'))
            ->when($filter === 'active', fn ($q) => $q->where('is_active', true)->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->when($filter === 'expired', fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now()))
            ->when($filter === 'disabled', fn ($q) => $q->where('is_active', false))
            ->when($filter === 'used', fn ($q) => $q->where('used_count', '>', 0))
            ->orderBy($request->query('sort') === 'usage' ? 'used_count' : 'created_at', 'desc')
            ->paginate(25)->withQueryString();

        return view('admin.coupons.index', compact('coupons', 'filter'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon(['type' => 'percent', 'is_active' => true, 'starts_at' => now()])]);
    }

    public function store(Request $request)
    {
        $c = Coupon::create($this->validated($request));
        ActivityLogger::log('coupon.created', $c, $c->code.' · '.$c->discountLabel());

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon '.$c->code.' created.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', [
            'coupon' => $coupon,
            'usages' => $coupon->usages()->with('order:id,order_number,customer_name,final_price,status,payment_status')->latest()->paginate(30),
        ]);
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));
        ActivityLogger::log('coupon.updated', $coupon, $coupon->code, $coupon->getChanges());

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon saved.');
    }

    public function destroy(Coupon $coupon)
    {
        ActivityLogger::log('coupon.deleted', $coupon, $coupon->code.' (used '.$coupon->used_count.'×)');
        $coupon->delete(); // orders keep coupon_code as a snapshot

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('code')))]);
        $d = $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01', $request->input('type') === 'percent' ? 'max:100' : 'max:100000'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_phone_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], ['code.regex' => 'Use letters, numbers, - and _ only.']);
        $d['is_active'] = $request->boolean('is_active');

        return $d;
    }
}
