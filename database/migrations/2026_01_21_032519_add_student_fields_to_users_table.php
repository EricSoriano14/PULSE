<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // If you already have production data, adjust column order/nullability as needed.
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');

            $table->string('student_id')->nullable()->after('last_name');
            $table->string('course')->nullable()->after('student_id');
            $table->string('department')->nullable()->after('course');
            $table->unsignedTinyInteger('year')->nullable()->after('department');

            $table->unique('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'student_id',
                'course',
                'department',
                'year',
            ]);
        });
    }
};
