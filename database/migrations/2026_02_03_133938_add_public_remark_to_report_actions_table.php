<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_actions', function (Blueprint $table) {
            if (!Schema::hasColumn('report_actions', 'public_remark')) {
                $table->text('public_remark')->nullable()->after('report_id');
            }

            if (!Schema::hasColumn('report_actions', 'action_taken_note')) {
                $table->text('action_taken_note')->nullable()->after('public_remark');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_actions', function (Blueprint $table) {
            if (Schema::hasColumn('report_actions', 'public_remark')) {
                $table->dropColumn('public_remark');
            }

            if (Schema::hasColumn('report_actions', 'action_taken_note')) {
                $table->dropColumn('action_taken_note');
            }
        });
    }
};
