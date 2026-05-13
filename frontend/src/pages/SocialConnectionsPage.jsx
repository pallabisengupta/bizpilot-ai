import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';

export function SocialConnectionsPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm font-semibold text-brand">Connections</p>
        <h1 className="mt-1 text-2xl font-bold text-text">Social media accounts</h1>
        <p className="mt-2 text-sm text-subtle">Connect Facebook pages, Instagram business accounts, and future WhatsApp integrations.</p>
      </div>
      <section className="grid gap-4 md:grid-cols-3">
        {['Facebook Page', 'Instagram Business', 'WhatsApp Business'].map((item) => (
          <Card key={item} className="p-5">
            <h2 className="font-bold text-text">{item}</h2>
            <p className="mt-2 text-sm text-subtle">OAuth connection flow will be enabled when provider credentials are configured.</p>
            <Button className="mt-5 w-full" variant="secondary">Connect</Button>
          </Card>
        ))}
      </section>
    </div>
  );
}
