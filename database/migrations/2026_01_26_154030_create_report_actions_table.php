<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_id')
                ->constrained('reports')
                ->cascadeOnDelete();

            // Faculty recommendation
            $table->foreignId('recommended_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('recommended_action', ['accept', 'decline'])->nullable();
            $table->text('recommended_note')->nullable();
            $table->timestamp('recommended_at')->nullable();

            // CSS / Admin decision
            $table->foreignId('decided_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('decision', ['accepted', 'declined'])->nullable();
            $table->text('decision_public_remark')->nullable();
            $table->text('decision_internal_note')->nullable();
            $table->timestamp('decision_at')->nullable();

            // Action taken
            $table->foreignId('action_taken_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('action_taken_note')->nullable();
            $table->timestamp('action_taken_at')->nullable();

            $table->timestamps();

            $table->unique('report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_actions');
    }
};
