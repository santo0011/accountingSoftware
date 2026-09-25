<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 15)->nullable()->index();
            $table->string('company')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 30)->default('website')->index();
            $table->string('status', 30)->default('new')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // set on conversion
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('next_followup_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20)->default('call'); // call | email | whatsapp | meeting
            $table->text('remarks');
            $table->timestamp('followup_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_followups');
        Schema::dropIfExists('leads');
    }
};
