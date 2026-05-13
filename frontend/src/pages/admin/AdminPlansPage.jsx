import { useEffect, useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Input } from '../../components/ui/Input';
import { Select } from '../../components/ui/Select';
import { useForm } from '../../hooks/useForm';
import { adminService } from '../../services/adminService';

export function AdminPlansPage() {
  const [plans, setPlans] = useState([]);
  const { values, handleChange, reset } = useForm({
    name: '',
    slug: '',
    price_amount: 0,
    currency: 'USD',
    billing_interval: 'monthly',
    features: '',
    is_active: true,
  });

  async function loadPlans() {
    const response = await adminService.plans();
    setPlans(response.data || []);
  }

  useEffect(() => {
    loadPlans().catch(() => {});
  }, []);

  async function handleSubmit(event) {
    event.preventDefault();
    await adminService.createPlan({
      ...values,
      price_amount: Number(values.price_amount),
      features: values.features ? values.features.split(',').map((feature) => feature.trim()) : [],
    });
    reset({ name: '', slug: '', price_amount: 0, currency: 'USD', billing_interval: 'monthly', features: '', is_active: true });
    await loadPlans();
  }

  return (
    <div className="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
      <Card className="p-5">
        <h1 className="text-xl font-bold text-text">Create plan</h1>
        <form className="mt-4 space-y-3" onSubmit={handleSubmit}>
          <Input label="Name" name="name" value={values.name} onChange={handleChange} required />
          <Input label="Slug" name="slug" value={values.slug} onChange={handleChange} required />
          <Input label="Price amount" name="price_amount" type="number" value={values.price_amount} onChange={handleChange} required />
          <Select label="Billing interval" name="billing_interval" value={values.billing_interval} onChange={handleChange}>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </Select>
          <Input label="Feature limits" name="features" value={values.features} onChange={handleChange} placeholder="AI 100, posts 50" />
          <Button type="submit" className="w-full">Save plan</Button>
        </form>
      </Card>
      <Card className="p-5">
        <h2 className="text-xl font-bold text-text">Plans</h2>
        <div className="mt-4 space-y-3">
          {plans.map((plan) => (
            <div key={plan.id} className="rounded-lg border border-border bg-canvas p-4">
              <p className="font-semibold text-text">{plan.name}</p>
              <p className="text-sm text-subtle">{plan.billing_interval} · {plan.currency} {plan.price_amount}</p>
            </div>
          ))}
        </div>
      </Card>
    </div>
  );
}
