import { LogOut, Menu, Moon, Sun } from 'lucide-react';
import { Button } from './ui/Button';
import { useApp } from '../store/AppContext';

export function Navbar() {
  const { logout, theme, toggleTheme, user, tenant } = useApp();

  return (
    <header className="flex min-h-16 items-center justify-between border-b border-border bg-panel px-4 md:px-6">
      <div className="flex items-center gap-3">
        <button className="focus-ring inline-flex h-9 w-9 items-center justify-center rounded-md text-subtle hover:bg-muted lg:hidden">
          <Menu size={20} aria-hidden="true" />
          <span className="sr-only">Open navigation</span>
        </button>
        <div>
          <p className="text-sm font-semibold text-text">{tenant?.company_name || 'Workspace setup'}</p>
          <p className="text-xs text-subtle">AI content, scheduling, WhatsApp, and CRM</p>
        </div>
      </div>

      <div className="flex items-center gap-2">
        <button
          className="focus-ring inline-flex h-9 w-9 items-center justify-center rounded-md text-subtle hover:bg-muted"
          onClick={toggleTheme}
          title="Toggle theme"
        >
          {theme === 'dark' ? <Sun size={18} /> : <Moon size={18} />}
          <span className="sr-only">Toggle theme</span>
        </button>
        <div className="hidden text-right sm:block">
          <p className="text-sm font-medium text-text">{user?.name || 'User'}</p>
          <p className="text-xs text-subtle">{user?.email || 'Signed in'}</p>
        </div>
        <Button variant="ghost" className="px-3" onClick={logout}>
          <LogOut size={17} />
          <span className="hidden sm:inline">Logout</span>
        </Button>
      </div>
    </header>
  );
}
