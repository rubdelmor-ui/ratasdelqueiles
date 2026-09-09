'use client'

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import type { NavContext } from '@/lib/nav';

function NotificationDot() {
  return (
    <span className="absolute top-0.5 right-2 w-2.5 h-2.5 bg-ember rounded-full border-2 border-surface animate-pulse" />
  );
}

function CountBadge({ count }: { count: number }) {
  if (count <= 0) return null;
  return (
    <span className="absolute -top-1.5 -right-1.5 bg-ember text-chrome text-[10px] font-bold font-mono rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 border-2 border-asphalt">
      {count}
    </span>
  );
}

interface NavLinksProps extends NavContext {
  variant: 'sidebar' | 'bottom';
}

const items = (nav: NavContext) => [
  { href: '/', icon: 'home_app_logo', label: 'Home', show: true },
  { href: '/salidas', icon: 'motorcycle', label: 'Salidas', show: true },
  { href: '/actas', icon: 'description', label: 'Actas', show: nav.esJunta, dot: nav.hayActasNuevas },
  { href: '/estatutos', icon: 'gavel', label: 'Estatutos', show: nav.esJunta },
  { href: '/socios', icon: 'groups', label: 'Socios', show: nav.esJunta, count: nav.pendientesTotal },
];

export default function NavLinks({ variant, ...nav }: NavLinksProps) {
  const activePath = usePathname();
  const list = items(nav).filter((i) => i.show);

  if (variant === 'sidebar') {
    return (
      <>
        {list.map((item) => {
          const active = activePath === item.href;
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`relative flex items-center gap-3 rounded-md px-3 py-3 font-mono text-sm uppercase tracking-wide transition-colors ${
                active
                  ? 'bg-rust/15 text-rust border-l-4 border-rust'
                  : 'text-smoke hover:text-chrome hover:bg-surface-high border-l-4 border-transparent'
              }`}
            >
              <span className="material-symbols-outlined" style={active ? { fontVariationSettings: "'FILL' 1" } : undefined}>
                {item.icon}
              </span>
              {item.label}
              {item.dot && <NotificationDot />}
              {!!item.count && <CountBadge count={item.count} />}
            </Link>
          );
        })}
      </>
    );
  }

  return (
    <>
      {list.map((item) => {
        const active = activePath === item.href;
        return (
          <Link
            key={item.href}
            href={item.href}
            className="relative flex flex-col items-center justify-center gap-0.5 flex-1 h-full"
          >
            <span
              className={`material-symbols-outlined text-[22px] transition-colors ${active ? 'text-rust' : 'text-smoke'}`}
              style={active ? { fontVariationSettings: "'FILL' 1" } : undefined}
            >
              {item.icon}
            </span>
            <span
              className={`font-mono text-[9px] uppercase tracking-wide transition-colors ${active ? 'text-rust' : 'text-ash'}`}
            >
              {item.label}
            </span>
            {active && <span className="absolute -top-[7px] w-6 h-[3px] rounded-full bg-rust gauge-glow" />}
            {item.dot && <NotificationDot />}
            {!!item.count && <CountBadge count={item.count} />}
          </Link>
        );
      })}
    </>
  );
}
