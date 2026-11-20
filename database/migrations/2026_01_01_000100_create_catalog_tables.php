<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('emoji', 16)->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->json('occasions')->nullable(); // e.g. ["Girlfriend","Proposal"]
            $table->string('accent_color', 20)->nullable();
            $table->string('icon_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('layout', 60); // blade layout key in TemplateRegistry
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('preview_path')->nullable();
            $table->json('theme')->nullable();   // colours, fonts
            $table->json('design')->nullable();  // layout options
            $table->unsignedSmallInteger('max_photos')->default(12);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('label');
            $table->string('type', 20); // text,textarea,date,email,url,phone,select,color,repeater,image,gallery
            $table->string('section', 40)->default('info'); // info | photos
            $table->string('placeholder')->nullable();
            $table->string('help')->nullable();
            $table->json('options')->nullable(); // select choices, repeater subfields, max items
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('max_length')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['template_id', 'key']);
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('duration_value');
            $table->string('duration_unit', 10); // day | month | year
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->string('description')->nullable();
            $table->json('features')->nullable(); // display bullets
            $table->json('flags')->nullable();    // max_photos, show_branding, premium_templates
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->string('type', 10); // percent | fixed
            $table->decimal('value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_phone_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'expires_at']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique(); // privacy, terms, refund-policy, about
            $table->string('title');
            $table->longText('body'); // markdown, rendered with HTML stripped
            $table->string('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['content_pages', 'faqs', 'testimonials', 'coupons', 'packages', 'template_fields', 'templates', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
