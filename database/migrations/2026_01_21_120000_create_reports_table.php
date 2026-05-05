<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('calamity');
            $table->string('department')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default('pending'); // pending|accepted|declined|action_taken
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

