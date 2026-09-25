<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('tagline', 120)->nullable()->after('icon');   // one short line for cards
            $table->string('image')->nullable()->after('tagline');       // uploaded card/hero image (public disk)
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'image']);
        });
    }
};
