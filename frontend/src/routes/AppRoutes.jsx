import { Navigate, Route, Routes } from 'react-router-dom';
import { AuthLayout } from '../layouts/AuthLayout';
import { AppLayout } from '../layouts/AppLayout';
import { AdminLayout } from '../layouts/AdminLayout';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';
import { DashboardPage } from '../pages/DashboardPage';
import { OnboardingPage } from '../pages/OnboardingPage';
import { SchedulesPage } from '../pages/SchedulesPage';
import { LeadsPage } from '../pages/LeadsPage';
import { AiContentPage } from '../pages/AiContentPage';
import { SocialConnectionsPage } from '../pages/SocialConnectionsPage';
import { AdminDashboardPage } from '../pages/admin/AdminDashboardPage';
import { AdminPlansPage } from '../pages/admin/AdminPlansPage';
import { AdminTenantsPage } from '../pages/admin/AdminTenantsPage';
import { AuthGuard, GuestGuard } from './AuthGuard';
import { AdminGuard } from './AdminGuard';

export function AppRoutes() {
  return (
    <Routes>
      <Route element={<GuestGuard />}>
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
        </Route>
      </Route>

      <Route element={<AuthGuard />}>
        <Route element={<AdminGuard />}>
          <Route element={<AdminLayout />}>
            <Route path="/admin" element={<AdminDashboardPage />} />
            <Route path="/admin/plans" element={<AdminPlansPage />} />
            <Route path="/admin/tenants" element={<AdminTenantsPage />} />
          </Route>
        </Route>

        <Route element={<AppLayout />}>
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/ai-content" element={<AiContentPage />} />
          <Route path="/connections" element={<SocialConnectionsPage />} />
          <Route path="/schedules" element={<SchedulesPage />} />
          <Route path="/leads" element={<LeadsPage />} />
          <Route path="/onboarding" element={<OnboardingPage />} />
        </Route>
      </Route>

      <Route path="/" element={<Navigate to="/dashboard" replace />} />
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}
