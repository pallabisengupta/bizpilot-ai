<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\AdminPlanController;
use App\Http\Controllers\Api\V1\Admin\AdminTenantController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\TenantOnboardingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'service' => 'bizpilot-ai-api',
                'version' => 'v1',
            ]);
        })->name('health');

        Route::prefix('auth')
            ->name('auth.')
            ->group(function (): void {
                Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
                Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
                Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
                Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

                Route::middleware('auth:sanctum')->group(function (): void {
                    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
                    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
                        ->middleware('signed')
                        ->name('verification.verify');
                    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                        ->middleware('throttle:6,1')
                        ->name('verification.send');
                });
            });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::prefix('admin')
                ->name('admin.')
                ->middleware('super_admin')
                ->group(function (): void {
                    Route::get('/analytics', AdminAnalyticsController::class)->name('analytics');
                    Route::apiResource('plans', AdminPlanController::class)->except(['show']);
                    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
                    Route::post('/tenants/{tenant}/activate', [AdminTenantController::class, 'activate'])->name('tenants.activate');
                    Route::post('/tenants/{tenant}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
                });

            Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
            Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');

            Route::get('/tenants/onboarding', [TenantOnboardingController::class, 'show'])->name('tenants.onboarding.show');
            Route::post('/tenants/onboarding', [TenantOnboardingController::class, 'store'])->name('tenants.onboarding.store');
            Route::post('/tenants/onboarding/step', [TenantOnboardingController::class, 'step'])->name('tenants.onboarding.step');

            Route::middleware('tenant')->group(function (): void {
                Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
                Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
                Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedules.show');
                Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
                Route::patch('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.patch');
                Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
                Route::post('/schedules/{schedule}/retry', [ScheduleController::class, 'retry'])->name('schedules.retry');

                Route::get('/leads/stats', [LeadController::class, 'stats'])->name('leads.stats');
                Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
                Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
                Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
                Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
                Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.patch');
                Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
                Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
                Route::post('/leads/{lead}/notes', [LeadController::class, 'note'])->name('leads.notes');
                Route::get('/leads/{lead}/activities', [LeadController::class, 'activities'])->name('leads.activities');

                Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
                Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
                Route::patch('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.patch');
                Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
            });
        });
    });
