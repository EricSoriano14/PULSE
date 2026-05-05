<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Phase 4 source-of-truth fields (added compatibly; do NOT remove existing columns).
            $table->enum('role', ['admin', 'css', 'faculty', 'student'])->nullable()->after('id');
            $table->string('full_name')->nullable()->after('role');
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('profile_image_url')->nullable()->after('username');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('profile_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['role', 'full_name', 'username', 'profile_image_url', 'status']);
        });
    }
};
