<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name', 150);
            $table->string('slug', 150)->unique();
            $table->string('industry', 100)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('timezone', 80)->default('UTC');
            $table->string('language', 20)->default('en');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('status', 30)->default('onboarding')->index();
            $table->json('onboarding')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('plan_id');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
