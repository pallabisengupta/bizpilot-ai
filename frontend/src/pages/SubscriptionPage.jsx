import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { subscriptionService } from '../services/subscriptionService';
import { loadRazorpay } from '../utils/razorpay';
import { useApp } from '../store/AppContext';
import { saveSelectedPlan } from '../utils/purchaseIntent';

export function SubscriptionPage() {
  const { user, tenant } = useApp();
  const navigate = useNavigate();
  const [plans, setPlans] = useState([]);
  const [current, setCurrent] = useState(null);
  const [error, setError] = useState('');

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

  async function subscribe(plan) {
    setError('');
    saveSelectedPlan(plan.id);

    if (!tenant) {
      navigate(`/onboarding?next=${encodeURIComponent(`/cart?plan=${plan.id}`)}`);
      return;
    }

    try {
      const checkout = await subscriptionService.checkout(plan.id);
      const Razorpay = await loadRazorpay();

      const payment = new Razorpay({
        key: checkout.key,
        amount: checkout.amount,
        currency: checkout.currency,
        name: checkout.name,
        description: checkout.description,
        order_id: checkout.order_id,
        prefill: {
          name: user?.name,
          email: user?.email,
          ...checkout.prefill,
        },
        handler: async (response) => {
          const subscription = await subscriptionService.verify({
            plan_id: plan.id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature,
          });
          setCurrent(subscription);
        },
        theme: {
          color: '#1070ca',
        },
      });

      payment.open();
    } catch (requestError) {
      const message = requestError.response?.data?.message || requestError.message || 'Unable to start checkout.';

      if (message === 'Tenant context is required.') {
        navigate(`/onboarding?next=${encodeURIComponent(`/cart?plan=${plan.id}`)}`);
        return;
      }

      setError(message);
    }
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
      {error ? <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}
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
            <Button className="mt-5 w-full" onClick={() => subscribe(plan)}>
              Subscribe
            </Button>
          </Card>
        ))}
      </section>
    </div>
  );
}
