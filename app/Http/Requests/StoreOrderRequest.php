<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageMedia;
use App\Models\Payment;
use App\Models\Template;
use App\Support\Bn;
use App\Support\ContentSchema;
use App\Support\Phone;
use App\Support\Settings;
use App\Support\SlugRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public ?Template $templateModel = null;

    public ?Package $packageModel = null;

    public ?ContentSchema $schema = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_phone' => Phone::normalize($this->input('customer_phone')),
            'payment_sender' => Phone::normalize($this->input('payment_sender')),
            'payment_trx' => strtoupper(preg_replace('/\s+/', '', (string) $this->input('payment_trx'))),
            'coupon_code' => strtoupper(trim((string) $this->input('coupon_code'))) ?: null,
            'desired_slug' => SlugRules::normalize($this->input('desired_slug')) ?: null,
            'customer_email' => strtolower(trim((string) $this->input('customer_email'))) ?: null,
        ]);

        $this->templateModel = Template::active()->with(['fields', 'category'])->where('slug', (string) $this->input('template'))->first();
        $this->packageModel = Package::active()->where('slug', (string) $this->input('package'))->first();
        $this->schema = $this->templateModel ? new ContentSchema($this->templateModel->fields) : null;
    }

    public function rules(): array
    {
        $rules = [
            'template' => ['required', 'string'],
            'package' => ['required', 'string'],
            'occasion' => ['nullable', 'string', 'max:80'],
            'customer_name' => ['required', 'string', 'min:2', 'max:80'],
            'customer_phone' => ['required', 'regex:'.Phone::BD_MOBILE],
            'customer_email' => ['nullable', 'email:rfc', 'max:120'],
            'desired_slug' => ['nullable', 'string', 'min:2', 'max:60', 'regex:'.SlugRules::PATTERN],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'media' => ['nullable', 'array'],
            'media.*' => ['array'],
            'media.*.*' => ['integer'],
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:140'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'payment_sender' => ['required', 'regex:'.Phone::BD_MOBILE],
            'payment_trx' => ['required', 'regex:/^[A-Z0-9]{8,20}$/'],
            'payment_amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'confirm' => ['accepted'],
        ];

        return $this->schema ? array_merge($rules, $this->schema->rules('content')) : $rules;
    }

    public function attributes(): array
    {
        return array_merge([
            'customer_name' => 'আপনার নাম', 'customer_phone' => 'ফোন নম্বর', 'customer_email' => 'ইমেইল',
            'desired_slug' => 'পেজের লিংক', 'payment_sender' => 'bKash নম্বর', 'payment_trx' => 'Transaction ID',
            'payment_amount' => 'টাকার পরিমাণ', 'confirm' => 'নিশ্চিতকরণ',
        ], $this->schema?->attributes('content') ?? []);
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'সঠিক মোবাইল নম্বর দিন (01XXXXXXXXX)।',
            'payment_sender.regex' => 'যে bKash নম্বর থেকে টাকা পাঠিয়েছেন সেটা দিন (01XXXXXXXXX)।',
            'payment_trx.regex' => 'Transaction ID-তে শুধু ইংরেজি অক্ষর ও সংখ্যা থাকে (৮–২০ অক্ষর)।',
            'desired_slug.regex' => 'লিংকে শুধু ইংরেজি ছোট অক্ষর, সংখ্যা আর হাইফেন (-) দিন।',
            'confirm.accepted' => 'জমা দেওয়ার আগে "সব তথ্য ঠিক আছে" টিক দিন।',
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if (! app(Settings::class)->bool('orders_enabled')) {
                $v->errors()->add('template', 'এই মুহূর্তে নতুন অর্ডার নেওয়া বন্ধ আছে। একটু পরে চেষ্টা করুন।');

                return;
            }
            if (! $this->templateModel) {
                $v->errors()->add('template', 'টেমপ্লেটটি পাওয়া যায়নি। আবার বেছে নিন।');

                return;
            }
            if (! $this->packageModel) {
                $v->errors()->add('package', 'প্যাকেজ বেছে নিন।');
            }

            if ($slug = $this->input('desired_slug')) {
                if (SlugRules::isReserved($slug)) {
                    $v->errors()->add('desired_slug', 'এই লিংকটি সংরক্ষিত। অন্য একটি নাম দিন।');
                } elseif (Page::where('slug', $slug)->exists()) {
                    $v->errors()->add('desired_slug', 'এই লিংকটি আগেই নেওয়া হয়েছে। অন্য একটি নাম দিন।');
                }
            }

            // Transaction IDs can't be reused across orders.
            if ($trx = $this->input('payment_trx')) {
                $used = Payment::where('transaction_id', $trx)->where('status', '!=', PaymentStatus::Rejected)->exists();
                if ($used) {
                    $v->errors()->add('payment_trx', 'এই Transaction ID দিয়ে আগেই একটি অর্ডার হয়েছে।');
                }
            }

            // Photos: only this browser's drafts count.
            $token = (string) $this->cookie('gb_draft');
            $ids = collect($this->input('media', []))->flatten()->map(fn ($x) => (int) $x)->filter()->all();
            $owned = $ids ? PageMedia::where('draft_token', $token)->whereNull('order_id')->whereIn('id', $ids)->get(['id', 'field_key']) : collect();
            foreach ($this->schema->mediaFields() as $f) {
                if ($f['required'] && $owned->where('field_key', $f['key'])->isEmpty()) {
                    $v->errors()->add('media.'.$f['key'], '"'.$f['label'].'" এর জন্য অন্তত একটি ছবি দিন।');
                }
            }
            $limit = min(
                (int) app(Settings::class)->get('max_files_per_order', 30),
                (int) ($this->packageModel?->maxPhotos() ?: 999),
                (int) ($this->templateModel->max_photos ?: 999),
            );
            if ($owned->where('field_key', '!=', 'music')->count() > $limit) {
                $v->errors()->add('media', 'এই প্যাকেজে সর্বোচ্চ '.Bn::digits($limit).'টি ছবি দেওয়া যায়। কিছু ছবি বাদ দিন বা বড় প্যাকেজ নিন।');
            }
        }];
    }
}
