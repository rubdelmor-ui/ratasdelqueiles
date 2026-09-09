'use client'

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import type { SessionPayload } from '@/lib/session';
import { logout } from '@/app/logout/actions';
import NotificationToggle from './NotificationToggle';

export default function SettingsMenu({ session }: { session: SessionPayload | null }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('click', onClickOutside);
    return () => document.removeEventListener('click', onClickOutside);
  }, []);

  return (
    <div className="relative" ref={ref}>
      <button
        onClick={() => setOpen((v) => !v)}
        className="text-smoke hover:text-rust hover:bg-surface-high transition-colors p-2 rounded-full"
        aria-label="Ajustes"
      >
        <span className="material-symbols-outlined">settings</span>
      </button>
      {open && (
        <div className="absolute right-0 mt-2 w-52 bg-surface border border-steel/70 rounded-lg shadow-[0_10px_30px_rgba(0,0,0,0.5)] py-1.5 z-50 overflow-hidden">
          {session ? (
            <>
              <div className="px-4 py-3 border-b border-steel/50">
                <span className="block text-chrome text-sm font-semibold truncate">{session.nombre}</span>
                <span className="eyebrow">{session.rol}</span>
              </div>
              <NotificationToggle />
              <form action={logout}>
                <button
                  type="submit"
                  className="w-full text-left flex items-center gap-2 px-4 py-2.5 text-smoke hover:bg-surface-high hover:text-rust transition-colors text-sm"
                >
                  <span className="material-symbols-outlined text-[18px]">logout</span>
                  Cerrar sesión
                </button>
              </form>
            </>
          ) : (
            <>
              <Link
                href="/login"
                className="flex items-center gap-2 px-4 py-2.5 text-smoke hover:bg-surface-high hover:text-rust transition-colors text-sm"
                onClick={() => setOpen(false)}
              >
                <span className="material-symbols-outlined text-[18px]">login</span>
                Iniciar sesión
              </Link>
              <Link
                href="/registro"
                className="flex items-center gap-2 px-4 py-2.5 text-smoke hover:bg-surface-high hover:text-rust transition-colors text-sm"
                onClick={() => setOpen(false)}
              >
                <span className="material-symbols-outlined text-[18px]">person_add</span>
                Registrarse
              </Link>
            </>
          )}
        </div>
      )}
    </div>
  );
}
