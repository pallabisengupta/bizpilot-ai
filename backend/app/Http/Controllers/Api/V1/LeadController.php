<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\AddLeadNoteRequest;
use App\Http\Requests\Leads\AssignLeadRequest;
use App\Http\Requests\Leads\IndexLeadRequest;
use App\Http\Requests\Leads\StoreLeadRequest;
use App\Http\Requests\Leads\UpdateLeadRequest;
use App\Http\Resources\LeadActivityResource;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leads,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function index(IndexLeadRequest $request): AnonymousResourceCollection
    {
        $leadPage = $this->leads->list(
            $this->tenantContext->get(),
            $request->validated(),
            (int) $request->integer('per_page', 15),
        );

        return LeadResource::collection($leadPage);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->leads->stats($this->tenantContext->get()),
        ]);
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->leads->create($this->tenantContext->get(), $request->user(), $request->validated());

        return response()->json([
            'message' => 'Lead created successfully.',
            'data' => [
                'lead' => new LeadResource($lead),
            ],
        ], 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $this->authorizeTenantLead($lead);

        return response()->json([
            'data' => [
                'lead' => new LeadResource($lead->load('assignedUser:id,name,email', 'activities.user:id,name,email')),
            ],
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $this->authorizeTenantLead($lead);
        $lead = $this->leads->update($lead, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Lead updated successfully.',
            'data' => [
                'lead' => new LeadResource($lead),
            ],
        ]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorizeTenantLead($lead);
        $this->leads->delete($lead);

        return response()->json([
            'message' => 'Lead deleted successfully.',
        ]);
    }

    public function assign(AssignLeadRequest $request, Lead $lead): JsonResponse
    {
        $this->authorizeTenantLead($lead);
        $lead = $this->leads->assign($lead, $request->user(), $request->validated('assigned_user_id'));

        return response()->json([
            'message' => 'Lead assignment updated successfully.',
            'data' => [
                'lead' => new LeadResource($lead),
            ],
        ]);
    }

    public function note(AddLeadNoteRequest $request, Lead $lead): JsonResponse
    {
        $this->authorizeTenantLead($lead);
        $lead = $this->leads->addNote($lead, $request->user(), $request->validated('note'));

        return response()->json([
            'message' => 'Lead note added successfully.',
            'data' => [
                'lead' => new LeadResource($lead),
            ],
        ]);
    }

    public function activities(Lead $lead): AnonymousResourceCollection
    {
        $this->authorizeTenantLead($lead);

        return LeadActivityResource::collection(
            $lead->activities()->with('user:id,name,email')->paginate(20),
        );
    }

    private function authorizeTenantLead(Lead $lead): void
    {
        abort_unless(
            (int) $lead->tenant_id === (int) $this->tenantContext->id(),
            403,
            'You do not have access to this lead.',
        );
    }
}
