<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('customer_code', 30)->unique();
            $table->string('alt_phone', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('business_type', 50)->nullable();
            $table->string('gstin', 15)->nullable()->index();
            $table->string('pan', 10)->nullable();
            $table->string('registration_no', 50)->nullable(); // CIN / LLPIN / Udyam
            $table->date('incorporation_date')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_code', 30)->nullable()->unique();
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->date('joined_on')->nullable();
            $table->timestamps();
        });

        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('professional_type', 50)->index();
            $table->string('registration_no', 100)->nullable();
            $table->string('specialization')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('customers');
    }
};
