<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('compliance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('frequency', 20); // monthly | quarterly | half_yearly | yearly | one_time
            $table->unsignedTinyInteger('due_day')->default(20);        // day of month the filing is due
            $table->unsignedTinyInteger('due_month_offset')->default(1); // months after period end
            $table->unsignedTinyInteger('reminder_days_before')->default(7);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 60)->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('full_description')->nullable();
            $table->json('who_needs')->nullable();
            $table->json('benefits')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->decimal('gst_rate', 5, 2)->default(18);
            $table->string('sac_code', 10)->nullable();
            $table->string('processing_time', 100)->nullable();
            $table->string('billing_type', 20)->default('one_time')->index(); // one_time | recurring
            $table->string('recurring_interval', 20)->nullable();           // monthly | quarterly | half_yearly | yearly
            $table->foreignId('compliance_type_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('name', 60);
            $table->string('type', 20)->default('text'); // text | number | email | date | select | textarea
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('placeholder')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['service_id', 'name']);
        });

        Schema::create('service_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('duration', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('group', 50)->default('general')->index();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('message');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['testimonials', 'faqs', 'pages', 'service_process_steps', 'service_faqs', 'service_fields',
            'service_documents', 'services', 'compliance_types', 'service_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
