export function Input({ label, error, className = '', ...props }) {
  return (
    <label className="block">
      <span className="mb-1.5 block text-sm font-medium text-text">{label}</span>
      <input
        className={`focus-ring h-11 w-full rounded-md border border-border bg-panel px-3 text-sm text-text placeholder:text-subtle ${className}`}
        {...props}
      />
      {error ? <span className="mt-1 block text-xs text-red-600">{error}</span> : null}
    </label>
  );
}
