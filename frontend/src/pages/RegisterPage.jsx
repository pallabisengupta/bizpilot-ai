import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { useForm } from '../hooks/useForm';
import { getErrorMessage } from '../utils/errors';
import { useApp } from '../store/AppContext';
import { useState } from 'react';

export function RegisterPage() {
  const { register } = useApp();
  const navigate = useNavigate();
  const location = useLocation();
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const searchParams = new URLSearchParams(location.search);
  const nextPath = searchParams.get('next');
  const emailParam = searchParams.get('email') || '';
  const loginSearch = location.search || (nextPath ? `?next=${encodeURIComponent(nextPath)}` : '');
  const { values, handleChange } = useForm({
    name: '',
    email: emailParam,
    password: '',
    password_confirmation: '',
    device_name: 'Web browser',
  });

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      await register(values);
      navigate(nextPath || '/dashboard', { replace: true });
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Card className="w-full max-w-md p-6">
      <div className="mb-6">
        <p className="text-sm font-semibold text-brand">Start your workspace</p>
        <h1 className="mt-2 text-2xl font-bold text-text">Create your account</h1>
        <p className="mt-2 text-sm text-subtle">Set up the account owner, then complete business onboarding.</p>
      </div>

      {error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <Input label="Full name" name="name" value={values.name} onChange={handleChange} required />
        <Input label="Email" name="email" type="email" value={values.email} onChange={handleChange} required />
        <Input label="Password" name="password" type="password" value={values.password} onChange={handleChange} required />
        <Input
          label="Confirm password"
          name="password_confirmation"
          type="password"
          value={values.password_confirmation}
          onChange={handleChange}
          required
        />
        <Button type="submit" className="w-full" isLoading={isSubmitting}>
          Register
        </Button>
      </form>

      <p className="mt-5 text-center text-sm text-subtle">
        Already have an account?{' '}
        <Link className="font-semibold text-brand" to={`/login${loginSearch}`}>Login</Link>
      </p>
    </Card>
  );
}
