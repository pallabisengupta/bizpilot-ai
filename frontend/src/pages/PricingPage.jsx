import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { authService } from '../services/authService';
import { subscriptionService } from '../services/subscriptionService';
import { useApp } from '../store/AppContext';
import { getErrorMessage } from '../utils/errors';
import { saveSelectedPlan } from '../utils/purchaseIntent';

export function PricingPage() {
  const { isAuthenticated, login } = useApp();
  const navigate = useNavigate();
  const { productUrl } = useParams();
  const [plans, setPlans] = useState([]);
  const [product, setProduct] = useState(null);
  const [products, setProducts] = useState([]);
  const [loadError, setLoadError] = useState('');
  const [selectedPlan, setSelectedPlan] = useState(null);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [step, setStep] = useState('email');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    const request = productUrl
      ? subscriptionService.productPlans(productUrl)
      : subscriptionService.plans().then((planList) => ({ plans: planList, product: null }));

    Promise.all([
      request,
      subscriptionService.products().catch(() => []),
    ])
      .then(([data, productList]) => {
        setPlans(data.plans);
        setProduct(data.product);
        setProducts(productList);
        setLoadError('');
      })
      .catch(() => {
        setPlans([]);
        setProduct(null);
        setLoadError('No active plans found for this product URL.');
      });
  }, [productUrl]);

  const otherProducts = useMemo(
    () => products.filter((item) => item.url && item.url !== productUrl),
    [products, productUrl],
  );

  function choosePlan(plan) {
    saveSelectedPlan(plan.id);

    if (isAuthenticated) {
      navigate(`/cart?plan=${plan.id}`);
      return;
    }

    setSelectedPlan(plan);
    setEmail('');
    setPassword('');
    setStep('email');
    setError('');
  }

  function registerUrl(planId, emailAddress) {
    const params = new URLSearchParams({
      plan: String(planId),
      next: '/cart',
      email: emailAddress,
    });

    return `/register?${params.toString()}`;
  }

  async function handleEmailSubmit(event) {
    event.preventDefault();

    if (!selectedPlan) {
      return;
    }

    setError('');
    setIsSubmitting(true);

    try {
      const result = await authService.checkEmail(email);

      if (result.exists) {
        setStep('password');
        return;
      }

      navigate(registerUrl(selectedPlan.id, email));
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handlePasswordSubmit(event) {
    event.preventDefault();

    if (!selectedPlan) {
      return;
    }

    setError('');
    setIsSubmitting(true);

    try {
      await login({
        email,
        password,
        device_name: 'Web browser',
      });
      navigate(`/cart?plan=${selectedPlan.id}`, { replace: true });
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="min-h-screen bg-canvas px-5 py-8 text-text">
      <nav className="mx-auto flex max-w-6xl items-center justify-between">
        <div>
          <p className="text-lg font-bold text-text">BizPilot AI</p>
          <p className="text-xs text-subtle">Social automation SaaS</p>
        </div>
        <div className="flex gap-2">
          <Link to="/login"><Button variant="secondary">Login</Button></Link>
          <Link to="/register"><Button>Start free</Button></Link>
        </div>
      </nav>

      <section className="mx-auto mt-12 max-w-6xl">
        <div className="grid gap-8 lg:grid-cols-[1.25fr_0.75fr] lg:items-end">
          <div>
            <p className="text-sm font-semibold text-brand">{product ? 'Product pricing' : 'Pricing'}</p>
            <h1 className="mt-2 max-w-4xl text-4xl font-bold leading-tight text-text md:text-5xl">
              {product ? `${product.name} subscription plans` : 'Sell smarter with one dashboard for content, scheduling, and leads.'}
            </h1>
            <p className="mt-4 max-w-2xl text-base text-subtle">
              {product?.description || 'Choose a plan, create your workspace, and pay securely with Razorpay checkout.'}
            </p>
          </div>
          <Card className="p-5">
            <p className="text-sm font-semibold text-text">What you get</p>
            <div className="mt-4 grid gap-3 text-sm text-subtle">
              <div className="rounded-md border border-border bg-canvas px-3 py-2">INR billing and Razorpay checkout</div>
              <div className="rounded-md border border-border bg-canvas px-3 py-2">Tenant workspace with secure login</div>
              <div className="rounded-md border border-border bg-canvas px-3 py-2">Upgrade-ready SaaS subscription flow</div>
            </div>
          </Card>
        </div>

        {loadError ? <div className="mt-6 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">{loadError}</div> : null}

        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {plans.map((plan, index) => (
            <Card key={plan.id} className={`p-6 ${index === 0 ? 'border-brand' : ''}`}>
              {index === 0 ? <p className="mb-3 text-xs font-bold uppercase text-brand">Popular</p> : null}
              <h2 className="text-xl font-bold text-text">{plan.name}</h2>
              <p className="mt-2 min-h-10 text-sm text-subtle">{plan.description || 'Business automation plan'}</p>
              <p className="mt-6 text-4xl font-bold text-text">{plan.currency || 'INR'} {plan.price_amount}</p>
              <p className="text-sm text-subtle">per {plan.billing_interval}</p>
              <div className="mt-5 space-y-2">
                {(Array.isArray(plan.features) ? plan.features : []).map((feature) => (
                  <p key={feature} className="rounded-md bg-muted px-3 py-2 text-sm text-subtle">{feature}</p>
                ))}
              </div>
              <Button className="mt-6 w-full" onClick={() => choosePlan(plan)}>Buy plan</Button>
            </Card>
          ))}
        </div>

        {!plans.length && !loadError ? (
          <Card className="mt-8 p-6">
            <p className="font-semibold text-text">No plans are active yet</p>
            <p className="mt-2 text-sm text-subtle">Create active plans for this product from the admin panel.</p>
          </Card>
        ) : null}

        <section className="mt-14">
          <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p className="text-sm font-semibold text-brand">Other products</p>
              <h2 className="mt-1 text-2xl font-bold text-text">Explore more BizPilot AI services</h2>
            </div>
            <Link className="text-sm font-semibold text-brand" to="/pricing">View all plans</Link>
          </div>
          <div className="mt-5 grid gap-4 md:grid-cols-3">
            {otherProducts.map((item) => (
              <Link key={item.id} to={`/pricing/${item.url}`}>
                <Card className="h-full p-5 transition hover:border-brand">
                  <p className="text-lg font-bold text-text">{item.name}</p>
                  <p className="mt-2 min-h-10 text-sm text-subtle">{item.description || 'Product-specific plans for this business area.'}</p>
                  <p className="mt-4 text-sm font-semibold text-brand">/pricing/{item.url}</p>
                  <p className="mt-1 text-xs text-subtle">{item.plans_count ?? 0} active plans</p>
                </Card>
              </Link>
            ))}
            {!otherProducts.length ? (
              <Card className="p-5">
                <p className="font-semibold text-text">More services coming soon</p>
                <p className="mt-2 text-sm text-subtle">Add products in the admin panel to show them here.</p>
              </Card>
            ) : null}
          </div>
        </section>
      </section>

      {selectedPlan ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
          <Card className="w-full max-w-md p-6">
            <div className="mb-5">
              <p className="text-sm font-semibold text-brand">{selectedPlan.name}</p>
              <h2 className="mt-1 text-xl font-bold text-text">
                {step === 'email' ? 'Enter your email to continue' : 'Welcome back'}
              </h2>
              <p className="mt-2 text-sm text-subtle">
                {step === 'email'
                  ? 'We will check whether you already have an account.'
                  : 'This email is already registered. Enter your password to continue to cart.'}
              </p>
            </div>

            {error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

            {step === 'email' ? (
              <form className="space-y-4" onSubmit={handleEmailSubmit}>
                <Input
                  label="Email"
                  name="email"
                  type="email"
                  autoComplete="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  required
                />
                <Button type="submit" className="w-full" isLoading={isSubmitting}>
                  Continue
                </Button>
              </form>
            ) : (
              <form className="space-y-4" onSubmit={handlePasswordSubmit}>
                <Input
                  label="Email"
                  name="email"
                  type="email"
                  value={email}
                  readOnly
                />
                <Input
                  label="Password"
                  name="password"
                  type="password"
                  autoComplete="current-password"
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  required
                />
                <Button type="submit" className="w-full" isLoading={isSubmitting}>
                  Login and go to cart
                </Button>
              </form>
            )}

            <div className="mt-4 flex justify-between gap-3">
              {step === 'password' ? (
                <Button variant="ghost" onClick={() => setStep('email')}>Use another email</Button>
              ) : <span />}
              <Button variant="secondary" onClick={() => setSelectedPlan(null)}>Cancel</Button>
            </div>
          </Card>
        </div>
      ) : null}
    </main>
  );
}
