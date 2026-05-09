import { Outlet } from 'react-router-dom';

export function AuthLayout() {
  return (
    <main className="grid min-h-screen bg-canvas lg:grid-cols-[1fr_0.9fr]">
      <section className="flex items-center justify-center px-5 py-10">
        <Outlet />
      </section>
      <section className="hidden border-l border-border bg-panel px-10 py-12 lg:flex lg:flex-col lg:justify-between">
        <div>
          <div className="mb-10 flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-md bg-brand font-bold text-white">
              BP
            </div>
            <div>
              <p className="font-bold text-text">BizPilot AI</p>
              <p className="text-sm text-subtle">SaaS growth operations</p>
            </div>
          </div>
          <h1 className="max-w-lg text-4xl font-bold leading-tight text-text">
            One focused dashboard for content, conversations, and leads.
          </h1>
        </div>
        <div className="grid grid-cols-2 gap-3">
          {['AI posts', 'Social scheduling', 'WhatsApp leads', 'CRM pipeline'].map((item) => (
            <div key={item} className="rounded-lg border border-border bg-canvas p-4">
              <p className="text-sm font-semibold text-text">{item}</p>
            </div>
          ))}
        </div>
      </section>
    </main>
  );
}
