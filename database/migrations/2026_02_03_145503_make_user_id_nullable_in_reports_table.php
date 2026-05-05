<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUserIdNullableInReportsTable extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
}
