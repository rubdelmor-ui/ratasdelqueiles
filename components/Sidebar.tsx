import Link from 'next/link';
import type { NavContext } from '@/lib/nav';
import NavLinks from './NavLinks';

export default function Sidebar(nav: NavContext) {
  return (
    <aside className="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface border-r-2 border-steel/60 p-4 z-30">
      <nav className="flex flex-col gap-1.5 mt-4">
        <NavLinks variant="sidebar" {...nav} />
      </nav>
      <div className="mt-auto pb-6 pt-4 border-t-2 border-dashed border-steel/50">
        <div className="cut-panel-sm bg-surface-high p-4 border border-steel/50 relative">
          {nav.esSuperadmin && (
            <Link
              href="/editar-home"
              title="Editar próxima reunión"
              className="absolute top-2 right-2 text-smoke hover:text-rust"
            >
              <span className="material-symbols-outlined text-[16px]">edit</span>
            </Link>
          )}
          <span className="eyebrow text-rust block mb-1">Próxima reunión</span>
          <span className="block display-text text-base text-chrome">{nav.reunionTexto}</span>
          <span className="block text-smoke text-xs mt-0.5">{nav.reunionLugar}</span>
        </div>
      </div>
    </aside>
  );
}
