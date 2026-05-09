export function Select({ label, children, className = '', ...props }) {
  return (
    <label className="block">
      <span className="mb-1.5 block text-sm font-medium text-text">{label}</span>
      <select
        className={`focus-ring h-11 w-full rounded-md border border-border bg-panel px-3 text-sm text-text ${className}`}
        {...props}
      >
        {children}
      </select>
    </label>
  );
}
