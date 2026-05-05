<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Receiver of the notification
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Notification type
            $table->string('type', 100);

            // Short display content
            $table->string('title', 255);
            $table->text('message');

            // Optional links / related records
            $table->foreignId('report_id')
                ->nullable()
                ->constrained('reports')
                ->nullOnDelete();

            $table->foreignId('announcement_id')
                ->nullable()
                ->constrained('announcements')
                ->nullOnDelete();

            // Optional sender / actor
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Optional target URL for web/mobile navigation
            $table->string('target_url')->nullable();

            // Read/unread support
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};