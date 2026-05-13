<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
        });

        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180)->nullable();
            $table->text('content');
            $table->string('platform', 50)->nullable();
            $table->json('media_urls')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'platform']);
        });

        Schema::create('ai_generations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('generation_type', 50);
            $table->text('prompt');
            $table->longText('output')->nullable();
            $table->string('status', 30)->default('completed')->index();
            $table->unsignedInteger('tokens_used')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'generation_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('campaigns');
    }
};
