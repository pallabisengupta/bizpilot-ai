import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { Select } from '../components/ui/Select';
import { useForm } from '../hooks/useForm';
import { tenantService } from '../services/tenantService';
import { useApp } from '../store/AppContext';
import { getErrorMessage } from '../utils/errors';

export function OnboardingPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { setTenant } = useApp();
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { values, handleChange, setValues } = useForm({
    company_name: '',
    industry: '',
    phone: '',
    email: '',
    website: '',
    timezone: 'UTC',
    language: 'en',
  });
  const nextPath = new URLSearchParams(location.search).get('next');

  useEffect(() => {
    tenantService.getOnboarding()
      .then((data) => {
        if (data.tenant) {
          setTenant(data.tenant);
          setValues((current) => ({
            ...current,
            company_name: data.tenant.company_name || '',
            industry: data.tenant.industry || '',
            phone: data.tenant.phone || '',
            email: data.tenant.email || '',
            website: data.tenant.website || '',
            timezone: data.tenant.timezone || 'UTC',
            language: data.tenant.language || 'en',
          }));
        }
      })
      .catch(() => {});
  }, [setTenant, setValues]);

  async function handleSaveStep() {
    setError('');

    try {
      const data = await tenantService.saveOnboardingStep({
        step: 'company_profile',
        company_name: values.company_name,
        timezone: values.timezone,
        language: values.language,
        payload: values,
      });
      setTenant(data.tenant);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      const data = await tenantService.completeOnboarding(values);
      setTenant(data.tenant);
      navigate(nextPath || '/dashboard', { replace: true });
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="mx-auto max-w-5xl space-y-6">
      <div>
        <p className="text-sm font-semibold text-brand">Onboarding</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Set up your business workspace</h1>
        <p className="mt-2 max-w-2xl text-sm text-subtle">
          Add the business details used for scheduling, WhatsApp lead intake, and CRM organization.
        </p>
      </div>

      <div className="grid gap-5 lg:grid-cols-[0.7fr_1.3fr]">
        <Card className="p-5">
          <h2 className="text-base font-bold text-text">Setup steps</h2>
          <div className="mt-4 space-y-3">
            {['Company profile', 'Business contact', 'Plan selection', 'Complete'].map((step, index) => (
              <div key={step} className="flex items-center gap-3">
                <span className="flex h-7 w-7 items-center justify-center rounded-md bg-brand/10 text-sm font-bold text-brand">
                  {index + 1}
                </span>
                <span className="text-sm font-medium text-text">{step}</span>
              </div>
            ))}
          </div>
        </Card>

        <Card className="p-5">
          {error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}
          <form className="grid gap-4 md:grid-cols-2" onSubmit={handleSubmit}>
            <div className="md:col-span-2">
              <Input label="Company name" name="company_name" value={values.company_name} onChange={handleChange} required />
            </div>
            <Input label="Industry" name="industry" value={values.industry} onChange={handleChange} placeholder="Retail, education, services" />
            <Input label="Phone" name="phone" value={values.phone} onChange={handleChange} />
            <Input label="Business email" name="email" type="email" value={values.email} onChange={handleChange} />
            <Input label="Website" name="website" type="url" value={values.website} onChange={handleChange} placeholder="https://example.com" />
            <Select label="Timezone" name="timezone" value={values.timezone} onChange={handleChange}>
              <option value="UTC">UTC</option>
              <option value="Asia/Kolkata">Asia/Kolkata</option>
              <option value="America/New_York">America/New_York</option>
              <option value="Europe/London">Europe/London</option>
            </Select>
            <Select label="Language" name="language" value={values.language} onChange={handleChange}>
              <option value="en">English</option>
              <option value="hi">Hindi</option>
              <option value="es">Spanish</option>
            </Select>
            <div className="flex flex-col gap-3 pt-2 sm:flex-row md:col-span-2">
              <Button type="submit" isLoading={isSubmitting}>Complete onboarding</Button>
              <Button type="button" variant="secondary" onClick={handleSaveStep}>Save step</Button>
            </div>
          </form>
        </Card>
      </div>
    </div>
  );
}
