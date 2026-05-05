<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_images', function (Blueprint $table) {
            if (!Schema::hasColumn('report_images', 'path')) {
                $table->string('path')->after('report_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_images', function (Blueprint $table) {
            if (Schema::hasColumn('report_images', 'path')) {
                $table->dropColumn('path');
            }
        });
    }
};
