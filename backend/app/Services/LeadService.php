<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\LeadRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function __construct(
        private readonly LeadRepository $leads,
    ) {
    }

    public function list(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->leads->paginateForTenant($tenant, $filters, $perPage);
    }

    public function stats(Tenant $tenant): array
    {
        return $this->leads->stats($tenant);
    }

    public function create(Tenant $tenant, User $user, array $data): Lead
    {
        return DB::transaction(function () use ($tenant, $user, $data): Lead {
            $this->ensureTenantAssignee($tenant, $data['assigned_user_id'] ?? null);

            $lead = $this->leads->create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'status' => $data['status'] ?? Lead::STATUS_NEW,
                'source' => $data['source'] ?? 'manual',
                'priority' => $data['priority'] ?? 'normal',
            ]));

            $this->recordActivity($lead, $user, 'created', 'Lead created', 'Lead record was created.');

            return $lead->load('assignedUser:id,name,email', 'activities.user:id,name,email');
        });
    }

    public function update(Lead $lead, User $user, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $user, $data): Lead {
            if (array_key_exists('assigned_user_id', $data)) {
                $this->ensureTenantAssignee($lead->tenant, $data['assigned_user_id']);
            }

            $oldStatus = $lead->status;
            $updated = $this->leads->update($lead, $data);

            if (($data['status'] ?? $oldStatus) !== $oldStatus) {
                $this->recordActivity($updated, $user, 'status_change', 'Status changed', 'Lead status was updated.', [
                    'status' => $oldStatus,
                ], [
                    'status' => $updated->status,
                ]);
            }

            return $updated;
        });
    }

    public function assign(Lead $lead, User $actor, ?int $assignedUserId): Lead
    {
        $this->ensureTenantAssignee($lead->tenant, $assignedUserId);

        return DB::transaction(function () use ($lead, $actor, $assignedUserId): Lead {
            $oldValue = ['assigned_user_id' => $lead->assigned_user_id];
            $updated = $this->leads->update($lead, ['assigned_user_id' => $assignedUserId]);

            $this->recordActivity($updated, $actor, 'assignment', 'Lead assigned', 'Lead assignment was updated.', $oldValue, [
                'assigned_user_id' => $assignedUserId,
            ]);

            return $updated;
        });
    }

    public function addNote(Lead $lead, User $user, string $note): Lead
    {
        return DB::transaction(function () use ($lead, $user, $note): Lead {
            $notes = trim(($lead->notes ? $lead->notes."\n\n" : '').$note);
            $updated = $this->leads->update($lead, ['notes' => $notes]);

            $this->recordActivity($updated, $user, 'note', 'Note added', $note);

            return $updated;
        });
    }

    public function delete(Lead $lead): void
    {
        $this->leads->delete($lead);
    }

    private function recordActivity(
        Lead $lead,
        User $user,
        string $type,
        string $title,
        ?string $description = null,
        ?array $oldValue = null,
        ?array $newValue = null,
    ): void {
        LeadActivity::query()->create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => $type,
            'title' => $title,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'occurred_at' => now(),
            'metadata' => [],
        ]);
    }

    private function ensureTenantAssignee(Tenant $tenant, ?int $assignedUserId): void
    {
        if (! $assignedUserId) {
            return;
        }

        $exists = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereKey($assignedUserId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'assigned_user_id' => ['Assigned user must belong to the same tenant.'],
            ]);
        }
    }
}
