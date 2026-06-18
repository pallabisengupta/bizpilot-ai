import { useEffect, useMemo, useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Select } from '../../components/ui/Select';
import { adminService } from '../../services/adminService';

function badgeClass(value) {
  if (['active', 'paid'].includes(value)) {
    return 'border-green-200 bg-green-50 text-green-700';
  }

  if (['pending', 'processing', 'past_due', 'onboarding'].includes(value)) {
    return 'border-amber-200 bg-amber-50 text-amber-700';
  }

  if (['suspended', 'cancelled', 'failed', 'expired', 'unpaid'].includes(value)) {
    return 'border-red-200 bg-red-50 text-red-700';
  }

  return 'border-border bg-muted text-subtle';
}

function StatusBadge({ value = 'not_started' }) {
  return (
    <span className={`inline-flex rounded-md border px-2 py-1 text-xs font-semibold ${badgeClass(value)}`}>
      {value.replaceAll('_', ' ')}
    </span>
  );
}

function formatMoney(subscription, fallbackPlan) {
  const amount = subscription?.amount ?? fallbackPlan?.price_amount;
  const currency = subscription?.currency ?? fallbackPlan?.currency;

  if (amount === undefined || amount === null || !currency) {
    return '-';
  }

  return `${currency} ${amount}`;
}

export function AdminTenantsPage() {
  const [tenants, setTenants] = useState([]);
  const [status, setStatus] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  async function loadTenants(nextStatus = status) {
    setIsLoading(true);
    try {
      const response = await adminService.tenants(nextStatus ? { status: nextStatus } : {});
      setTenants(response.data || []);
    } finally {
      setIsLoading(false);
    }
  }

  useEffect(() => {
    loadTenants().catch(() => {});
  }, []);

  async function updateStatus(id, action) {
    if (action === 'activate') await adminService.activateTenant(id);
    if (action === 'suspend') await adminService.suspendTenant(id);
    await loadTenants();
  }

  function handleStatusChange(event) {
    const nextStatus = event.target.value;
    setStatus(nextStatus);
    loadTenants(nextStatus).catch(() => {});
  }

  const totals = useMemo(() => ({
    total: tenants.length,
    paid: tenants.filter((tenant) => tenant.latest_subscription?.billing_status === 'paid').length,
    pending: tenants.filter((tenant) => ['pending', 'processing', 'unpaid'].includes(tenant.latest_subscription?.billing_status)).length,
    noSubscription: tenants.filter((tenant) => !tenant.latest_subscription).length,
  }), [tenants]);

  return (
    <div className="space-y-5">
      <div className="grid gap-4 md:grid-cols-4">
        <Card className="p-4">
          <p className="text-sm text-subtle">Total clients</p>
          <p className="mt-2 text-2xl font-bold text-text">{totals.total}</p>
        </Card>
        <Card className="p-4">
          <p className="text-sm text-subtle">Paid</p>
          <p className="mt-2 text-2xl font-bold text-text">{totals.paid}</p>
        </Card>
        <Card className="p-4">
          <p className="text-sm text-subtle">Payment in process</p>
          <p className="mt-2 text-2xl font-bold text-text">{totals.pending}</p>
        </Card>
        <Card className="p-4">
          <p className="text-sm text-subtle">No subscription</p>
          <p className="mt-2 text-2xl font-bold text-text">{totals.noSubscription}</p>
        </Card>
      </div>

      <Card className="p-5">
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-sm font-semibold text-brand">Admin</p>
            <h1 className="mt-1 text-xl font-bold text-text">Tenant management</h1>
            <p className="mt-2 text-sm text-subtle">Monitor client workspaces, owners, plans, payment state, and usage.</p>
          </div>
          <div className="w-full md:w-56">
            <Select label="Tenant status" value={status} onChange={handleStatusChange}>
              <option value="">All statuses</option>
              <option value="onboarding">Onboarding</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="cancelled">Cancelled</option>
            </Select>
          </div>
        </div>

        <div className="mt-5 overflow-x-auto">
          <table className="w-full min-w-[980px] border-separate border-spacing-y-3 text-left text-sm">
            <thead className="text-xs uppercase text-subtle">
              <tr>
                <th className="px-3 py-2">Client</th>
                <th className="px-3 py-2">Owner</th>
                <th className="px-3 py-2">Plan</th>
                <th className="px-3 py-2">Subscription</th>
                <th className="px-3 py-2">Payment</th>
                <th className="px-3 py-2">Usage</th>
                <th className="px-3 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {tenants.map((tenant) => {
                const subscription = tenant.latest_subscription;
                const plan = subscription?.plan || tenant.plan;

                return (
                  <tr key={tenant.id} className="rounded-lg bg-canvas">
                    <td className="rounded-l-lg border-y border-l border-border px-3 py-4 align-top">
                      <p className="font-semibold text-text">{tenant.company_name}</p>
                      <p className="mt-1 text-xs text-subtle">{tenant.slug}</p>
                      <div className="mt-2"><StatusBadge value={tenant.status} /></div>
                      <p className="mt-2 text-xs text-subtle">{tenant.email || 'No business email'}</p>
                      <p className="text-xs text-subtle">{tenant.phone || 'No phone'}</p>
                    </td>
                    <td className="border-y border-border px-3 py-4 align-top">
                      <p className="font-medium text-text">{tenant.owner?.name || '-'}</p>
                      <p className="mt-1 text-xs text-subtle">{tenant.owner?.email || '-'}</p>
                      <p className="mt-1 text-xs text-subtle">{tenant.users_count ?? 0} users</p>
                    </td>
                    <td className="border-y border-border px-3 py-4 align-top">
                      <p className="font-medium text-text">{plan?.name || 'No plan'}</p>
                      <p className="mt-1 text-xs text-subtle">{plan?.slug || '-'}</p>
                      <p className="mt-1 text-xs text-subtle">{formatMoney(subscription, tenant.plan)} {subscription?.billing_interval || tenant.plan?.billing_interval || ''}</p>
                    </td>
                    <td className="border-y border-border px-3 py-4 align-top">
                      <StatusBadge value={subscription?.status || 'not_started'} />
                      <p className="mt-2 text-xs text-subtle">Ends: {subscription?.current_period_ends_at || '-'}</p>
                    </td>
                    <td className="border-y border-border px-3 py-4 align-top">
                      <StatusBadge value={subscription?.billing_status || 'not_started'} />
                      <p className="mt-2 text-xs text-subtle">Amount: {formatMoney(subscription, tenant.plan)}</p>
                    </td>
                    <td className="border-y border-border px-3 py-4 align-top">
                      <p className="text-xs text-subtle">Leads: <span className="font-semibold text-text">{tenant.leads_count ?? 0}</span></p>
                      <p className="mt-1 text-xs text-subtle">Schedules: <span className="font-semibold text-text">{tenant.schedules_count ?? 0}</span></p>
                    </td>
                    <td className="rounded-r-lg border-y border-r border-border px-3 py-4 align-top">
                      <div className="flex flex-col gap-2">
                        <Button variant="secondary" onClick={() => updateStatus(tenant.id, 'activate')}>Activate</Button>
                        <Button variant="secondary" onClick={() => updateStatus(tenant.id, 'suspend')}>Suspend</Button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>

          {!tenants.length ? (
            <div className="rounded-lg border border-border bg-canvas p-6 text-center text-sm text-subtle">
              {isLoading ? 'Loading tenants...' : 'No tenants found.'}
            </div>
          ) : null}
        </div>
      </Card>
    </div>
  );
}
