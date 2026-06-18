import { useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { subscriptionService } from '../services/subscriptionService';
import { getSelectedPlanId, saveSelectedPlan } from '../utils/purchaseIntent';
import { useApp } from '../store/AppContext';

export function CartPage() {
  const { tenant } = useApp();
  const location = useLocation();
  const navigate = useNavigate();
  const [plans, setPlans] = useState([]);
  const [error, setError] = useState('');
  const selectedPlanId = getSelectedPlanId(location.search);

  useEffect(() => {
    if (selectedPlanId) {
      saveSelectedPlan(selectedPlanId);
    }

    if (!tenant && selectedPlanId) {
      navigate(`/onboarding?next=${encodeURIComponent(`/cart?plan=${selectedPlanId}`)}`, { replace: true });
      return;
    }

    subscriptionService.plans()
      .then(setPlans)
      .catch(() => setError('Unable to load plans right now.'));
  }, [navigate, selectedPlanId, tenant]);

  const plan = useMemo(
    () => plans.find((item) => String(item.id) === String(selectedPlanId)),
    [plans, selectedPlanId],
  );

  function continueToCheckout() {
    if (!plan) {
      return;
    }

    saveSelectedPlan(plan.id);
    navigate(`/checkout?plan=${plan.id}`);
  }

  return (
    <div className="mx-auto grid max-w-5xl gap-5 lg:grid-cols-[1.25fr_0.75fr]">
      <section>
        <p className="text-sm font-semibold text-brand">Cart</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Review your subscription</h1>
        <p className="mt-2 text-sm text-subtle">Confirm the plan before opening secure checkout.</p>

        {error ? <div className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

        {!selectedPlanId ? (
          <Card className="mt-5 p-5">
            <p className="font-semibold text-text">No plan selected</p>
            <p className="mt-2 text-sm text-subtle">Choose a plan from pricing to continue.</p>
            <Link to="/pricing">
              <Button className="mt-4">View pricing</Button>
            </Link>
          </Card>
        ) : null}

        {selectedPlanId && !plan && !error ? (
          <Card className="mt-5 p-5">
            <p className="font-semibold text-text">Loading selected plan...</p>
          </Card>
        ) : null}

        {plan ? (
          <Card className="mt-5 p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h2 className="text-xl font-bold text-text">{plan.name}</h2>
                <p className="mt-2 max-w-xl text-sm text-subtle">{plan.description || 'Subscription plan for your workspace.'}</p>
              </div>
              <div className="sm:text-right">
                <p className="text-3xl font-bold text-text">{plan.currency} {plan.price_amount}</p>
                <p className="text-sm text-subtle">per {plan.billing_interval}</p>
              </div>
            </div>
            <div className="mt-5 grid gap-2 sm:grid-cols-2">
              {(Array.isArray(plan.features) ? plan.features : []).map((feature) => (
                <div key={feature} className="rounded-md border border-border bg-canvas px-3 py-2 text-sm text-subtle">
                  {feature}
                </div>
              ))}
            </div>
          </Card>
        ) : null}
      </section>

      <Card className="h-fit p-5">
        <h2 className="text-lg font-bold text-text">Order summary</h2>
        <div className="mt-4 space-y-3 text-sm">
          <div className="flex justify-between gap-3">
            <span className="text-subtle">Plan</span>
            <span className="font-semibold text-text">{plan?.name || 'Not selected'}</span>
          </div>
          <div className="flex justify-between gap-3">
            <span className="text-subtle">Billing</span>
            <span className="font-semibold text-text">{plan?.billing_interval || '-'}</span>
          </div>
          <div className="border-t border-border pt-3">
            <div className="flex justify-between gap-3">
              <span className="font-semibold text-text">Total due</span>
              <span className="font-bold text-text">{plan ? `${plan.currency} ${plan.price_amount}` : '-'}</span>
            </div>
          </div>
        </div>
        <Button className="mt-5 w-full" disabled={!plan} onClick={continueToCheckout}>
          Continue to checkout
        </Button>
      </Card>
    </div>
  );
}
