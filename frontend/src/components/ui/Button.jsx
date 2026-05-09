const variants = {
  primary: 'bg-brand text-white hover:bg-brand/90',
  secondary: 'bg-muted text-text hover:bg-border/60',
  ghost: 'bg-transparent text-subtle hover:bg-muted hover:text-text',
};

export function Button({
  type = 'button',
  variant = 'primary',
  className = '',
  isLoading = false,
  children,
  ...props
}) {
  return (
    <button
      type={type}
      className={`focus-ring inline-flex h-10 items-center justify-center gap-2 rounded-md px-4 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
      disabled={isLoading || props.disabled}
      {...props}
    >
      {isLoading ? 'Please wait...' : children}
    </button>
  );
}
