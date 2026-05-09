import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { useForm } from '../hooks/useForm';
import { getErrorMessage } from '../utils/errors';
import { useApp } from '../store/AppContext';
import { useState } from 'react';

export function LoginPage() {
  const { login } = useApp();
  const navigate = useNavigate();
  const location = useLocation();
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { values, handleChange } = useForm({
    email: '',
    password: '',
    device_name: 'Web browser',
  });

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      await login(values);
      navigate(location.state?.from?.pathname || '/dashboard', { replace: true });
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Card className="w-full max-w-md p-6">
      <div className="mb-6">
        <p className="text-sm font-semibold text-brand">Welcome back</p>
        <h1 className="mt-2 text-2xl font-bold text-text">Sign in to BizPilot AI</h1>
        <p className="mt-2 text-sm text-subtle">Manage your business workspace and growth workflows.</p>
      </div>

      {error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <Input
          label="Email"
          name="email"
          type="email"
          autoComplete="email"
          value={values.email}
          onChange={handleChange}
          required
        />
        <Input
          label="Password"
          name="password"
          type="password"
          autoComplete="current-password"
          value={values.password}
          onChange={handleChange}
          required
        />
        <Button type="submit" className="w-full" isLoading={isSubmitting}>
          Login
        </Button>
      </form>

      <p className="mt-5 text-center text-sm text-subtle">
        New to BizPilot AI?{' '}
        <Link className="font-semibold text-brand" to="/register">Create an account</Link>
      </p>
    </Card>
  );
}
