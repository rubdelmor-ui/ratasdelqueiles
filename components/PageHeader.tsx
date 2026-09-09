export default function PageHeader({
  eyebrow,
  title,
  subtitle,
  action,
}: {
  eyebrow?: string;
  title: string;
  subtitle?: string;
  action?: React.ReactNode;
}) {
  return (
    <div>
      {eyebrow && (
        <span className="eyebrow flex items-center gap-2 mb-1">
          <span className="w-4 h-[2px] bg-rust" /> {eyebrow}
        </span>
      )}
      <div className="flex items-center justify-between gap-3">
        <h2 className="display-text text-2xl text-chrome">{title}</h2>
        {action}
      </div>
      {subtitle && <p className="text-smoke text-sm mt-1">{subtitle}</p>}
    </div>
  );
}
