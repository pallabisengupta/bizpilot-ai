import {
  Bot,
  CalendarClock,
  LayoutDashboard,
  MessageCircle,
  Settings,
  UsersRound,
} from 'lucide-react';
import { NavLink } from 'react-router-dom';

const navItems = [
  { label: 'Dashboard', to: '/dashboard', icon: LayoutDashboard },
  { label: 'AI Content', to: '/ai-content', icon: Bot },
  { label: 'Connections', to: '/connections', icon: MessageCircle },
  { label: 'Schedules', to: '/schedules', icon: CalendarClock },
  { label: 'Leads', to: '/leads', icon: UsersRound },
  { label: 'Settings', to: '/onboarding', icon: Settings },
];

export function Sidebar() {
  return (
    <aside className="hidden w-64 shrink-0 border-r border-border bg-panel px-4 py-5 lg:block">
      <div className="mb-7 flex items-center gap-3 px-2">
        <div className="flex h-10 w-10 items-center justify-center rounded-md bg-brand text-sm font-bold text-white">
          BP
        </div>
        <div>
          <p className="text-sm font-bold text-text">BizPilot AI</p>
          <p className="text-xs text-subtle">Business command center</p>
        </div>
      </div>

      <nav className="space-y-1">
        {navItems.map((item) => {
          const Icon = item.icon;

          return (
            <NavLink
              key={item.label}
              to={item.to}
              className={({ isActive }) => `flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition ${
                isActive
                  ? 'bg-brand/10 text-brand'
                  : 'text-subtle hover:bg-muted hover:text-text'
              }`}
            >
              <Icon size={18} aria-hidden="true" />
              {item.label}
            </NavLink>
          );
        })}
      </nav>
    </aside>
  );
}
