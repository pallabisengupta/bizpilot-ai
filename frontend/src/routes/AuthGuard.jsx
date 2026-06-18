import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useApp } from '../store/AppContext';

export function AuthGuard() {
  const { isAuthenticated } = useApp();
  const location = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return <Outlet />;
}

export function GuestGuard() {
  const { isAuthenticated } = useApp();
  const location = useLocation();
  const nextPath = new URLSearchParams(location.search).get('next');

  if (isAuthenticated) {
    return <Navigate to={nextPath || '/dashboard'} replace />;
  }

  return <Outlet />;
}
