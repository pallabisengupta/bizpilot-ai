import { useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { subscriptionService } from '../services/subscriptionService';
import { useApp } from '../store/AppContext';
import { clearSelectedPlan, getSelectedPlanId, saveSelectedPlan } from '../utils/purchaseIntent';
import { loadRazorpay } from '../utils/razorpay';

export function CheckoutPage() {
  const { user, tenant } = useApp();
  const location = useLocation();
  const navigate = useNavigate();
  const [plans, setPlans] = useState([]);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const selectedPlanId = getSelectedPlanId(location.search);

  useEffect(() => {
    if (selectedPlanId) {
      saveSelectedPlan(selectedPlanId);
    }

    if (!tenant && selectedPlanId) {
      navigate(`/onboarding?next=${encodeURIComponent(`/checkout?plan=${selectedPlanId}`)}`, { replace: true });
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

  async function startCheckout() {
    if (!plan) {
      return;
    }

    setError('');
    setSuccess('');
    setIsSubmitting(true);

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
          await subscriptionService.verify({
            plan_id: plan.id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature,
          });
          clearSelectedPlan();
          setSuccess('Payment verified. Your subscription is active.');
          setIsSubmitting(false);
        },
        modal: {
          ondismiss: () => setIsSubmitting(false),
        },
        theme: {
          color: '#1070ca',
        },
      });

      payment.open();
    } catch (requestError) {
      const message = requestError.response?.data?.message || requestError.message || 'Unable to start checkout.';

      if (message === 'Tenant context is required.') {
        navigate(`/onboarding?next=${encodeURIComponent(`/checkout?plan=${plan.id}`)}`);
        return;
      }

      setError(message);
      setIsSubmitting(false);
    }
  }

  return (
    <div className="mx-auto max-w-3xl space-y-5">
      <div>
        <p className="text-sm font-semibold text-brand">Checkout</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Complete your subscription</h1>
        <p className="mt-2 text-sm text-subtle">Payment is collected securely through Razorpay.</p>
      </div>

      {error ? <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}
      {success ? <div className="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{success}</div> : null}

      {!selectedPlanId ? (
        <Card className="p-5">
          <p className="font-semibold text-text">No plan selected</p>
          <p className="mt-2 text-sm text-subtle">Return to pricing and choose a subscription plan.</p>
          <Link to="/pricing">
            <Button className="mt-4">View pricing</Button>
          </Link>
        </Card>
      ) : null}

      {plan ? (
        <Card className="p-5">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h2 className="text-xl font-bold text-text">{plan.name}</h2>
              <p className="mt-2 text-sm text-subtle">{plan.description || 'Subscription plan for your workspace.'}</p>
            </div>
            <div className="sm:text-right">
              <p className="text-3xl font-bold text-text">{plan.currency} {plan.price_amount}</p>
              <p className="text-sm text-subtle">per {plan.billing_interval}</p>
            </div>
          </div>
          <Button className="mt-6 w-full" isLoading={isSubmitting} onClick={startCheckout}>
            Pay with Razorpay
          </Button>
          {success ? (
            <Link to="/dashboard">
              <Button variant="secondary" className="mt-3 w-full">Go to dashboard</Button>
            </Link>
          ) : null}
        </Card>
      ) : null}
    </div>
  );
}
