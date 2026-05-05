<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('password');
            $table->string('address')->nullable()->after('avatar_path');
            $table->text('info')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'address', 'info']);
        });
    }
};

