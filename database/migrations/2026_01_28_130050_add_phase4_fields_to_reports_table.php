<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Phase 4 source-of-truth fields (added compatibly).
            $table->foreignId('student_id')->nullable()->after('user_id')
                ->constrained('students')->nullOnDelete();

            $table->string('calamity_type')->nullable()->after('student_id');
            $table->string('category')->nullable()->after('calamity_type');
            $table->string('affected_subject_or_class')->nullable()->after('category');

            $table->decimal('latitude', 10, 7)->nullable()->after('description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Keep Phase 3 fields intact (user_id, calamity, department, etc.).
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn([
                'student_id',
                'calamity_type',
                'category',
                'affected_subject_or_class',
                'latitude',
                'longitude',
            ]);
        });
    }
};
