<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Optional link to users (for token auth). Keep nullable to avoid breaking existing web data.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('student_id_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('year_level');

            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
