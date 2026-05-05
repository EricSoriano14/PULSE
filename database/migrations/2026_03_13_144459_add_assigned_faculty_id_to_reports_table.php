<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('assigned_faculty_id')
                ->nullable()
                ->after('department')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_faculty_id');
        });
    }
};