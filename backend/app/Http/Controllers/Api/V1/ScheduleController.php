<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\IndexScheduleRequest;
use App\Http\Requests\Schedules\StoreScheduleRequest;
use App\Http\Requests\Schedules\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Services\ScheduleService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $schedules,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function index(IndexScheduleRequest $request): AnonymousResourceCollection
    {
        $schedulePage = $this->schedules->list(
            $this->tenantContext->get(),
            $request->validated(),
            (int) $request->integer('per_page', 15),
        );

        return ScheduleResource::collection($schedulePage);
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = $this->schedules->create(
            $this->tenantContext->get(),
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => 'Schedule queued successfully.',
            'data' => [
                'schedule' => new ScheduleResource($schedule),
            ],
        ], 201);
    }

    public function show(Schedule $schedule): JsonResponse
    {
        $this->authorizeTenantSchedule($schedule);

        return response()->json([
            'data' => [
                'schedule' => new ScheduleResource($schedule),
            ],
        ]);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): JsonResponse
    {
        $this->authorizeTenantSchedule($schedule);

        $schedule = $this->schedules->update($schedule, $request->validated());

        return response()->json([
            'message' => 'Schedule updated and queued successfully.',
            'data' => [
                'schedule' => new ScheduleResource($schedule),
            ],
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorizeTenantSchedule($schedule);
        $this->schedules->delete($schedule);

        return response()->json([
            'message' => 'Schedule deleted successfully.',
        ]);
    }

    public function retry(Schedule $schedule): JsonResponse
    {
        $this->authorizeTenantSchedule($schedule);

        $schedule = $this->schedules->retry($schedule);

        return response()->json([
            'message' => 'Failed schedule queued for retry.',
            'data' => [
                'schedule' => new ScheduleResource($schedule),
            ],
        ]);
    }

    private function authorizeTenantSchedule(Schedule $schedule): void
    {
        abort_unless(
            (int) $schedule->tenant_id === (int) $this->tenantContext->id(),
            403,
            'You do not have access to this schedule.',
        );
    }
}
