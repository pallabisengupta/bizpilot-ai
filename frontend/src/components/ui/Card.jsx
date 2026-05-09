export function Card({ className = '', children }) {
  return (
    <section className={`rounded-lg border border-border bg-panel shadow-soft ${className}`}>
      {children}
    </section>
  );
}
