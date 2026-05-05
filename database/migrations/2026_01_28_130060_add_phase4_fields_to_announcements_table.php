<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Phase 4 source-of-truth fields (added compatibly).
            $table->foreignId('posted_by_user_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();

            $table->string('image_url')->nullable()->after('image_path');

            $table->foreignId('department_target_id')->nullable()->after('image_url')
                ->constrained('departments')->nullOnDelete();

            $table->foreignId('course_target_id')->nullable()->after('department_target_id')
                ->constrained('courses')->nullOnDelete();

            $table->enum('status', ['active', 'canceled'])->default('active')->after('course_target_id');
            $table->timestamp('canceled_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['posted_by_user_id']);
            $table->dropForeign(['department_target_id']);
            $table->dropForeign(['course_target_id']);

            $table->dropColumn([
                'posted_by_user_id',
                'image_url',
                'department_target_id',
                'course_target_id',
                'status',
                'canceled_at',
            ]);
        });
    }
};
