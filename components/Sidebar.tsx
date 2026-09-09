import type { NavContext } from '@/lib/nav';
import NavLinks from './NavLinks';

export default function Sidebar(nav: NavContext) {
  return (
    <aside className="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface border-r-2 border-steel/60 p-4 z-30">
      <nav className="flex flex-col gap-1.5 mt-4">
        <NavLinks variant="sidebar" {...nav} />
      </nav>
      <div className="mt-auto pb-6 pt-4 border-t-2 border-dashed border-steel/50">
        <div className="cut-panel-sm bg-surface-high p-4 border border-steel/50">
          <span className="eyebrow text-rust block mb-1">Próxima reunión</span>
          <span className="block display-text text-base text-chrome">Viernes · 20:00</span>
          <span className="block text-smoke text-xs mt-0.5">Sede del Club</span>
        </div>
      </div>
    </aside>
  );
}
