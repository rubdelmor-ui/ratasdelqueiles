import type { NavContext } from '@/lib/nav';
import NavLinks from './NavLinks';

export default function BottomNav(nav: NavContext) {
  return (
    <nav className="md:hidden fixed bottom-0 left-0 w-full z-40 pb-safe">
      <div className="mx-3 mb-3 h-[64px] flex items-stretch bg-surface/95 backdrop-blur-md border border-steel/60 rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.5)]">
        <NavLinks variant="bottom" {...nav} />
      </div>
    </nav>
  );
}
