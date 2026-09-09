'use client'

import { useState } from 'react';
import type { AsistenteConAcompanantes } from '@/lib/salidas';

export default function AsistentesButton({
  salidaId,
  total,
  asistentes,
  esSuperadmin,
}: {
  salidaId: string;
  total: number;
  asistentes: AsistenteConAcompanantes[];
  esSuperadmin: boolean;
}) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button onClick={() => setOpen(true)} className="skew-chip bg-surface-higher border-steel/60">
        <span className="skew-chip-inner text-smoke text-[11px] px-2.5 py-1">
          <span className="material-symbols-outlined text-[14px]">group</span>
          {total}
        </span>
      </button>

      {open && (
        <div className="sheet-overlay" onClick={() => setOpen(false)}>
          <div className="sheet" onClick={(e) => e.stopPropagation()}>
            <div className="sheet-handle" />
            <div className="flex items-center gap-2 pb-3 mb-1 border-b border-dashed border-steel/50">
              <span className="material-symbols-outlined text-rust">group</span>
              <h3 className="display-text text-lg text-chrome">Asistentes a la salida</h3>
            </div>
            <ul className="list-none p-0 m-0 divide-y divide-steel/30">
              {asistentes.length === 0 && (
                <li className="text-ash py-4 text-sm text-center">No hay asistentes apuntados todavía.</li>
              )}
              {asistentes.map((item, i) => (
                <li key={i}>
                  <div className="flex items-center gap-2 py-2.5">
                    <span className="material-symbols-outlined text-rust text-[20px]">person</span>
                    <span className="text-chrome">{item.socio}</span>
                  </div>
                  {item.acompanantes.map((acomp, j) => (
                    <div key={j} className="flex items-center gap-2 py-2 pl-8 text-sm text-smoke">
                      <span className="material-symbols-outlined text-[16px]">person_add</span>
                      {acomp} <span className="text-ash">(acompañante)</span>
                    </div>
                  ))}
                </li>
              ))}
            </ul>
            {esSuperadmin && (
              <a
                href={`/api/salidas/${salidaId}/excel`}
                target="_blank"
                rel="noreferrer"
                className="btn btn-success btn-block mt-4"
              >
                <span className="material-symbols-outlined text-[20px]">download</span>
                Descargar Excel
              </a>
            )}
            <button onClick={() => setOpen(false)} className="btn btn-ghost btn-block mt-2">
              Cerrar
            </button>
          </div>
        </div>
      )}
    </>
  );
}
