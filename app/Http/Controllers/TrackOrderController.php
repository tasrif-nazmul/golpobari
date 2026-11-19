<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Phone;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function form()
    {
        return view('site.track', ['seo' => ['title' => 'অর্ডার ট্র্যাক · GolpoBari', 'noindex' => true]]);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
        ], [], ['order_number' => 'Order ID', 'phone' => 'ফোন নম্বর']);

        $order = $this->find($data['order_number'], $data['phone']);
        if (! $order) {
            return back()->withInput()->withErrors(['order_number' => 'এই Order ID আর ফোন নম্বর দিয়ে কোনো অর্ডার পাওয়া যায়নি।']);
        }

        $request->session()->put('tracked_order', $order->id);

        return view('site.track-result', [
            'order' => $order->load(['payment', 'page']),
            'seo' => ['title' => 'অর্ডার '.$order->order_number.' · GolpoBari', 'noindex' => true],
        ]);
    }

    /** After a rejected payment the customer can send a corrected Transaction ID. */
    public function resubmitPayment(Request $request)
    {
        $order = Order::find((int) $request->session()->get('tracked_order'));
        abort_unless($order && $order->payment_status === PaymentStatus::Rejected, 403);

        $request->merge([
            'payment_sender' => Phone::normalize($request->input('payment_sender')),
            'payment_trx' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('payment_trx'))),
        ]);
        $data = $request->validate([
            'payment_sender' => ['required', 'regex:'.Phone::BD_MOBILE],
            'payment_trx' => ['required', 'regex:/^[A-Z0-9]{8,20}$/'],
            'payment_amount' => ['required', 'numeric', 'min:1', 'max:100000'],
        ], ['payment_sender.regex' => 'সঠিক bKash নম্বর দিন।', 'payment_trx.regex' => 'সঠিক Transaction ID দিন।']);

        if (Payment::where('transaction_id', $data['payment_trx'])->where('status', '!=', PaymentStatus::Rejected)->exists()) {
            return back()->withErrors(['payment_trx' => 'এই Transaction ID আগেই ব্যবহার হয়েছে।']);
        }

        $order->payments()->create([
            'method' => 'bkash', 'amount' => $data['payment_amount'], 'expected_amount' => $order->final_price,
            'receiver_number' => app(\App\Support\Settings::class)->bkashNumber(),
            'sender_number' => $data['payment_sender'], 'transaction_id' => $data['payment_trx'], 'status' => PaymentStatus::Pending,
        ]);
        $order->update(['payment_status' => PaymentStatus::Pending]);
        $order->histories()->create(['from_status' => $order->status->value, 'to_status' => OrderStatus::PaymentPending->value, 'note' => 'Customer re-submitted payment ('.$data['payment_trx'].')']);
        if ($order->status !== OrderStatus::PaymentPending) {
            $order->update(['status' => OrderStatus::PaymentPending]);
        }

        return redirect()->route('track')->with('status', 'ধন্যবাদ! নতুন পেমেন্ট তথ্য পেয়েছি, শীঘ্রই যাচাই করা হবে।');
    }

    private function find(string $number, string $phone): ?Order
    {
        $order = Order::where('order_number', strtoupper(trim($number)))->first();

        return $order && hash_equals($order->customer_phone, Phone::normalize($phone)) ? $order : null;
    }
}
