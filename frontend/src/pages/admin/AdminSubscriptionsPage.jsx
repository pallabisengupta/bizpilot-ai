import { useEffect, useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { adminService } from '../../services/adminService';

export function AdminSubscriptionsPage() {
  const [subscriptions, setSubscriptions] = useState([]);

  async function load() {
    const response = await adminService.subscriptions();
    setSubscriptions(response.data || []);
  }

  useEffect(() => {
    load().catch(() => {});
  }, []);

  async function markPaid(id) {
    await adminService.markSubscriptionPaid(id);
    await load();
  }

  async function cancel(id) {
    await adminService.cancelSubscription(id);
    await load();
  }

  return (
    <Card className="p-5">
      <h1 className="text-xl font-bold text-text">Subscription management</h1>
      <div className="mt-4 space-y-3">
        {subscriptions.map((subscription) => (
          <div key={subscription.id} className="flex flex-col justify-between gap-3 rounded-lg border border-border bg-canvas p-4 md:flex-row md:items-center">
            <div>
              <p className="font-semibold text-text">{subscription.tenant?.company_name || `Tenant #${subscription.tenant_id}`}</p>
              <p className="text-sm text-subtle">{subscription.plan?.name || `Plan #${subscription.plan_id}`} · {subscription.status} · {subscription.billing_status}</p>
              <p className="text-xs text-subtle">{subscription.currency} {subscription.amount} / {subscription.billing_interval}</p>
            </div>
            <div className="flex gap-2">
              <Button variant="secondary" onClick={() => markPaid(subscription.id)}>Mark paid</Button>
              <Button variant="secondary" onClick={() => cancel(subscription.id)}>Cancel</Button>
            </div>
          </div>
        ))}
      </div>
    </Card>
  );
}
