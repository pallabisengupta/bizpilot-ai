import { useEffect, useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Input } from '../../components/ui/Input';
import { Select } from '../../components/ui/Select';
import { useForm } from '../../hooks/useForm';
import { adminService } from '../../services/adminService';

export function AdminPlansPage() {
  const [plans, setPlans] = useState([]);
  const [products, setProducts] = useState([]);
  const [features, setFeatures] = useState([]);
  const [selectedFeatures, setSelectedFeatures] = useState([]);
  const [newFeatureName, setNewFeatureName] = useState('');
  const [newProduct, setNewProduct] = useState({
    name: '',
    url: '',
    description: '',
  });
  const { values, handleChange, reset } = useForm({
    product_id: '',
    name: '',
    slug: '',
    price_amount: 0,
    currency: 'INR',
    billing_interval: 'monthly',
    is_active: true,
  });

  async function loadData() {
    const [response, featureResponse, productResponse] = await Promise.all([
      adminService.plans(),
      adminService.planFeatures(),
      adminService.products(),
    ]);

    setPlans(response.data || []);
    setFeatures(featureResponse.data || []);
    setProducts(productResponse.data || []);
  }

  useEffect(() => {
    loadData().catch(() => {});
  }, []);

  function handleFeatureSelect(event) {
    setSelectedFeatures(Array.from(event.target.selectedOptions).map((option) => option.value));
  }

  async function handleAddFeature(event) {
    event.preventDefault();

    if (!newFeatureName.trim()) {
      return;
    }

    const feature = await adminService.createPlanFeature({ name: newFeatureName.trim() });
    setNewFeatureName('');
    setFeatures((current) => [...current, feature].sort((first, second) => first.name.localeCompare(second.name)));
    setSelectedFeatures((current) => [...new Set([...current, feature.name])]);
  }

  async function handleAddProduct(event) {
    event.preventDefault();

    if (!newProduct.name.trim()) {
      return;
    }

    const product = await adminService.createProduct({
      name: newProduct.name.trim(),
      url: newProduct.url.trim(),
      description: newProduct.description.trim() || null,
    });
    setNewProduct({ name: '', url: '', description: '' });
    setProducts((current) => [...current, product].sort((first, second) => first.name.localeCompare(second.name)));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    await adminService.createPlan({
      ...values,
      product_id: Number(values.product_id),
      price_amount: Number(values.price_amount),
      features: selectedFeatures,
    });
    reset({ product_id: '', name: '', slug: '', price_amount: 0, currency: 'INR', billing_interval: 'monthly', is_active: true });
    setSelectedFeatures([]);
    await loadData();
  }

  return (
    <div className="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
      <Card className="p-5">
        <h1 className="text-xl font-bold text-text">Create plan</h1>
        <form className="mt-4 space-y-3" onSubmit={handleSubmit}>
          <Select label="Product" name="product_id" value={values.product_id} onChange={handleChange} required>
            <option value="">Select product</option>
            {products.map((product) => (
              <option key={product.id} value={product.id}>{product.name}</option>
            ))}
          </Select>
          <Input label="Name" name="name" value={values.name} onChange={handleChange} required />
          <Input label="Slug" name="slug" value={values.slug} onChange={handleChange} required />
          <Input label="Price amount" name="price_amount" type="number" value={values.price_amount} onChange={handleChange} required />
          <Select label="Billing interval" name="billing_interval" value={values.billing_interval} onChange={handleChange}>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </Select>
          <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-text">Features</span>
            <select
              multiple
              className="focus-ring min-h-32 w-full rounded-md border border-border bg-panel px-3 py-2 text-sm text-text"
              value={selectedFeatures}
              onChange={handleFeatureSelect}
            >
              {features.map((feature) => (
                <option key={feature.id} value={feature.name}>{feature.name}</option>
              ))}
            </select>
            <span className="mt-1 block text-xs text-subtle">Hold Ctrl or Cmd to select multiple features.</span>
          </label>
          <Button type="submit" className="w-full">Save plan</Button>
        </form>
      </Card>

      <Card className="p-5">
        <h2 className="text-xl font-bold text-text">Plans by product</h2>
        <div className="mt-4 space-y-3">
          {products.map((product) => {
            const productPlans = plans.filter((plan) => Number(plan.product_id) === Number(product.id));

            return (
              <div key={product.id} className="rounded-lg border border-border bg-canvas p-4">
                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <p className="font-semibold text-text">{product.name}</p>
                    <p className="text-xs text-brand">/pricing/{product.url}</p>
                  </div>
                  <span className="text-xs font-semibold text-subtle">{productPlans.length} plans</span>
                </div>

                <div className="mt-3 space-y-2">
                  {productPlans.map((plan) => (
                    <div key={plan.id} className="rounded-md border border-border bg-panel p-3">
                      <p className="font-medium text-text">{plan.name}</p>
                      <p className="text-sm text-subtle">{plan.billing_interval} - {plan.currency} {plan.price_amount}</p>
                      <div className="mt-2 flex flex-wrap gap-2">
                        {(Array.isArray(plan.features) ? plan.features : []).map((feature) => (
                          <span key={feature} className="rounded-md bg-muted px-2 py-1 text-xs font-medium text-subtle">{feature}</span>
                        ))}
                      </div>
                    </div>
                  ))}
                  {!productPlans.length ? (
                    <p className="rounded-md border border-dashed border-border px-3 py-2 text-sm text-subtle">No plans created for this product yet.</p>
                  ) : null}
                </div>
              </div>
            );
          })}
        </div>
      </Card>

      <Card className="p-5 xl:col-span-2">
        <h2 className="text-xl font-bold text-text">Products</h2>
        <form className="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_1.4fr_auto]" onSubmit={handleAddProduct}>
          <Input
            label="Product name"
            name="product_name"
            value={newProduct.name}
            onChange={(event) => setNewProduct((current) => ({ ...current, name: event.target.value }))}
            placeholder="Restaurants"
          />
          <Input
            label="URL"
            name="product_url"
            value={newProduct.url}
            onChange={(event) => setNewProduct((current) => ({ ...current, url: event.target.value }))}
            placeholder="restaurants"
          />
          <Input
            label="Description"
            name="product_description"
            value={newProduct.description}
            onChange={(event) => setNewProduct((current) => ({ ...current, description: event.target.value }))}
            placeholder="Plans for restaurant businesses"
          />
          <Button type="submit" className="md:mt-7">Add product</Button>
        </form>
        <div className="mt-4 grid gap-3 md:grid-cols-3">
          {products.map((product) => (
            <div key={product.id} className="rounded-lg border border-border bg-canvas p-4">
              <p className="font-semibold text-text">{product.name}</p>
              <p className="mt-1 text-sm text-brand">/pricing/{product.url}</p>
              <p className="mt-1 text-xs text-subtle">{product.plans_count ?? 0} plans</p>
            </div>
          ))}
        </div>
      </Card>

      <Card className="p-5 xl:col-span-2">
        <h2 className="text-xl font-bold text-text">Feature options</h2>
        <form className="mt-4 flex flex-col gap-3 sm:flex-row" onSubmit={handleAddFeature}>
          <Input
            label="New feature option"
            name="feature"
            value={newFeatureName}
            onChange={(event) => setNewFeatureName(event.target.value)}
            placeholder="AI captions, 100 scheduled posts, WhatsApp inbox"
          />
          <Button type="submit" className="sm:mt-7">Add option</Button>
        </form>
        <div className="mt-4 flex flex-wrap gap-2">
          {features.map((feature) => (
            <span key={feature.id} className="rounded-md border border-border bg-canvas px-3 py-2 text-sm text-subtle">
              {feature.name}
            </span>
          ))}
        </div>
      </Card>
    </div>
  );
}
