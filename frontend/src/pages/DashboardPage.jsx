import { Bot, CalendarCheck, MessageSquareText, TrendingUp, UsersRound } from 'lucide-react';
import { Card } from '../components/ui/Card';

const metrics = [
  { label: 'Generated posts', value: '128', change: '+18%', icon: Bot },
  { label: 'Scheduled posts', value: '34', change: '+9%', icon: CalendarCheck },
  { label: 'WhatsApp leads', value: '76', change: '+24%', icon: MessageSquareText },
  { label: 'CRM pipeline', value: '214', change: '+12%', icon: UsersRound },
];

const activities = [
  'Instagram campaign draft approved',
  'WhatsApp lead assigned to sales',
  'Facebook post scheduled for Friday',
  'AI content template updated',
];

export function DashboardPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
          <p className="text-sm font-semibold text-brand">Dashboard</p>
          <h1 className="mt-1 text-2xl font-bold text-text">Business growth overview</h1>
          <p className="mt-2 max-w-2xl text-sm text-subtle">
            Track content creation, social scheduling, WhatsApp conversations, and lead movement from one workspace.
          </p>
        </div>
        <div className="rounded-md border border-border bg-panel px-4 py-3">
          <p className="text-xs text-subtle">This month</p>
          <p className="text-sm font-semibold text-text">18.6% engagement lift</p>
        </div>
      </div>

      <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        {metrics.map((metric) => {
          const Icon = metric.icon;

          return (
            <Card key={metric.label} className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-subtle">{metric.label}</p>
                  <p className="mt-2 text-3xl font-bold text-text">{metric.value}</p>
                </div>
                <div className="flex h-10 w-10 items-center justify-center rounded-md bg-brand/10 text-brand">
                  <Icon size={20} />
                </div>
              </div>
              <p className="mt-4 flex items-center gap-1 text-sm font-medium text-success">
                <TrendingUp size={16} />
                {metric.change} from last month
              </p>
            </Card>
          );
        })}
      </section>

      <section className="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
        <Card className="p-5">
          <div className="mb-5 flex items-center justify-between">
            <div>
              <h2 className="text-lg font-bold text-text">Publishing pipeline</h2>
              <p className="text-sm text-subtle">Upcoming social content by workflow stage</p>
            </div>
          </div>
          <div className="grid gap-3 md:grid-cols-3">
            {['Draft', 'Approved', 'Scheduled'].map((stage, index) => (
              <div key={stage} className="rounded-lg border border-border bg-canvas p-4">
                <p className="text-sm font-semibold text-text">{stage}</p>
                <p className="mt-2 text-2xl font-bold text-text">{[18, 11, 34][index]}</p>
                <div className="mt-4 h-2 rounded-full bg-muted">
                  <div className="h-2 rounded-full bg-accent" style={{ width: `${[52, 36, 78][index]}%` }} />
                </div>
              </div>
            ))}
          </div>
        </Card>

        <Card className="p-5">
          <h2 className="text-lg font-bold text-text">Recent activity</h2>
          <div className="mt-4 space-y-3">
            {activities.map((activity) => (
              <div key={activity} className="rounded-md border border-border bg-canvas px-3 py-3">
                <p className="text-sm font-medium text-text">{activity}</p>
                <p className="mt-1 text-xs text-subtle">Just now</p>
              </div>
            ))}
          </div>
        </Card>
      </section>
    </div>
  );
}
