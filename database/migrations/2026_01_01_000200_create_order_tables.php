<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->nullable()->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->index();
            $table->string('customer_email')->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->json('snapshot')->nullable(); // names/prices at time of order
            $table->string('occasion')->nullable();
            $table->string('coupon_code', 40)->nullable();
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('manual_discount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->string('status', 30)->index();
            $table->string('payment_status', 20)->index();
            $table->json('content')->nullable();
            $table->string('desired_slug', 80)->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('draft_token', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20)->default('bkash');
            $table->decimal('amount', 10, 2);          // amount customer says they sent
            $table->decimal('expected_amount', 10, 2); // amount we asked for
            $table->string('receiver_number', 20)->nullable();
            $table->string('sender_number', 20);
            $table->string('transaction_id', 40)->index();
            $table->string('status', 20)->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('note')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->index();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->constrained()->restrictOnDelete();
            $table->string('slug', 80)->unique();
            $table->string('title');
            $table->string('status', 20)->index();      // draft,pending,active,expired,suspended,archived
            $table->string('visibility', 20)->default('public'); // public,unlisted,private
            $table->string('access_code')->nullable();  // hashed code for private pages
            $table->json('content')->nullable();
            $table->json('theme')->nullable();          // per-page overrides
            $table->json('seo')->nullable();
            $table->boolean('show_branding')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('expiry_reminded_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('page_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('draft_token', 64)->nullable()->index();
            $table->string('field_key', 60);
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->string('mime', 50);
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['page_id', 'field_key', 'sort_order']);
            $table->index(['order_id', 'field_key']);
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_hash', 64);
            $table->string('device', 10);
            $table->string('referrer_host')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['page_id', 'viewed_at']);
            $table->index(['page_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        foreach (['page_views', 'page_media', 'pages', 'coupon_usages', 'order_status_histories', 'payments', 'orders'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
