import { useEffect, useState } from 'react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { subscriptionService } from '../services/subscriptionService';

export function SubscriptionPage() {
  const [plans, setPlans] = useState([]);
  const [current, setCurrent] = useState(null);

  async function load() {
    const [planList, subscription] = await Promise.all([
      subscriptionService.plans(),
      subscriptionService.current().catch(() => null),
    ]);
    setPlans(planList);
    setCurrent(subscription);
  }

  useEffect(() => {
    load().catch(() => {});
  }, []);

  async function subscribe(planId) {
    const subscription = await subscriptionService.subscribe(planId);
    setCurrent(subscription);
  }

  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm font-semibold text-brand">Subscription</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Choose a plan</h1>
        <p className="mt-2 text-sm text-subtle">Activate or change the current tenant subscription.</p>
      </div>
      {current ? (
        <Card className="p-5">
          <p className="text-sm text-subtle">Current plan</p>
          <p className="mt-1 text-xl font-bold text-text">{current.plan?.name || `Plan #${current.plan_id}`}</p>
          <p className="mt-1 text-sm text-subtle">{current.status} · {current.billing_status}</p>
        </Card>
      ) : null}
      <section className="grid gap-4 md:grid-cols-3">
        {plans.map((plan) => (
          <Card key={plan.id} className="p-5">
            <h2 className="text-lg font-bold text-text">{plan.name}</h2>
            <p className="mt-2 text-sm text-subtle">{plan.description || 'Subscription plan'}</p>
            <p className="mt-5 text-3xl font-bold text-text">{plan.currency} {plan.price_amount}</p>
            <p className="text-sm text-subtle">{plan.billing_interval}</p>
            <div className="mt-4 space-y-2">
              {(Array.isArray(plan.features) ? plan.features : []).map((feature) => (
                <p key={feature} className="text-sm text-subtle">{feature}</p>
              ))}
            </div>
            <Button className="mt-5 w-full" onClick={() => subscribe(plan.id)}>
              Subscribe
            </Button>
          </Card>
        ))}
      </section>
    </div>
  );
}
