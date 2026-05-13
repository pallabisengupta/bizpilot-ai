import { BarChart3, Building2, CreditCard, LayoutDashboard } from 'lucide-react';
import { NavLink } from 'react-router-dom';

const navItems = [
  { label: 'Admin Overview', to: '/admin', icon: LayoutDashboard },
  { label: 'Plans', to: '/admin/plans', icon: CreditCard },
  { label: 'Subscriptions', to: '/admin/subscriptions', icon: BarChart3 },
  { label: 'Tenants', to: '/admin/tenants', icon: Building2 },
];

export function AdminSidebar() {
  return (
    <aside className="hidden w-64 shrink-0 border-r border-border bg-panel px-4 py-5 lg:block">
      <div className="mb-7 px-2">
        <p className="text-sm font-bold text-text">BizPilot Admin</p>
        <p className="text-xs text-subtle">Platform operations</p>
      </div>
      <nav className="space-y-1">
        {navItems.map((item) => {
          const Icon = item.icon;
          return (
            <NavLink
              key={item.label}
              to={item.to}
              end={item.to === '/admin'}
              className={({ isActive }) => `flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition ${
                isActive ? 'bg-brand/10 text-brand' : 'text-subtle hover:bg-muted hover:text-text'
              }`}
            >
              <Icon size={18} />
              {item.label}
            </NavLink>
          );
        })}
      </nav>
    </aside>
  );
}
