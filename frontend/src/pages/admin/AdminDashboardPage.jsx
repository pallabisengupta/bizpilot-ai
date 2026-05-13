import { useEffect, useState } from 'react';
import { BarChart3, Bot, Building2, CalendarClock, UsersRound } from 'lucide-react';
import { Card } from '../../components/ui/Card';
import { adminService } from '../../services/adminService';

const icons = {
  total_clients: Building2,
  active_subscriptions: BarChart3,
  total_leads: UsersRound,
  scheduled_posts: CalendarClock,
  ai_usage: Bot,
};

export function AdminDashboardPage() {
  const [analytics, setAnalytics] = useState({});

  useEffect(() => {
    adminService.analytics().then(setAnalytics).catch(() => {});
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm font-semibold text-brand">Super Admin</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Platform analytics</h1>
      </div>
      <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        {Object.entries({
          total_clients: 'Total clients',
          active_subscriptions: 'Active subscriptions',
          total_leads: 'Total leads',
          scheduled_posts: 'Scheduled posts',
          ai_usage: 'AI usage',
        }).map(([key, label]) => {
          const Icon = icons[key];
          return (
            <Card key={key} className="p-5">
              <Icon className="text-brand" size={22} />
              <p className="mt-4 text-sm text-subtle">{label}</p>
              <p className="mt-2 text-3xl font-bold text-text">{analytics[key] ?? 0}</p>
            </Card>
          );
        })}
      </section>
    </div>
  );
}
