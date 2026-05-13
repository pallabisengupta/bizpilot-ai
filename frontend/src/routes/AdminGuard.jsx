import { Navigate, Outlet } from 'react-router-dom';
import { useApp } from '../store/AppContext';

export function AdminGuard() {
  const { user } = useApp();

  if (user?.role !== 'super_admin') {
    return <Navigate to="/dashboard" replace />;
  }

  return <Outlet />;
}
