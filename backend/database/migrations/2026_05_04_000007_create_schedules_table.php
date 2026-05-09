<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180)->nullable();
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->string('platform', 50);
            $table->timestamp('scheduled_at')->index();
            $table->string('timezone', 80)->default('UTC');
            $table->string('status', 30)->default('queued')->index();
            $table->string('provider_post_id', 255)->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('created_by_user_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'platform', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
