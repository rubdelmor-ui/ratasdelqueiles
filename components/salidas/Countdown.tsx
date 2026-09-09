'use client'

import { useEffect, useState } from 'react';

function calcularTexto(fechaIso: string): { texto: string; terminado: boolean } {
  if (!fechaIso) return { texto: 'FECHA NO DISPONIBLE', terminado: false };
  const target = new Date(fechaIso).getTime();
  if (isNaN(target)) return { texto: 'FECHA NO VÁLIDA', terminado: false };

  const diff = target - Date.now();
  if (diff <= 0) return { texto: '¡EN RUTA!', terminado: true };

  const d = Math.floor(diff / 86400000);
  const h = Math.floor((diff % 86400000) / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  return {
    texto: `${d > 0 ? d + 'D ' : ''}${String(h).padStart(2, '0')}H ${String(m).padStart(2, '0')}M ${String(s).padStart(2, '0')}S`,
    terminado: false,
  };
}

export default function Countdown({ fechaIso }: { fechaIso: string }) {
  const [estado, setEstado] = useState(() => calcularTexto(fechaIso));

  useEffect(() => {
    const id = setInterval(() => setEstado(calcularTexto(fechaIso)), 1000);
    return () => clearInterval(id);
  }, [fechaIso]);

  return (
    <div className="flex items-center gap-2">
      <span className={`material-symbols-outlined text-[16px] ${estado.terminado ? 'text-hazard' : 'text-rust'}`}>
        {estado.terminado ? 'sports_motorsports' : 'schedule'}
      </span>
      <span
        className={`gauge-number gauge-glow text-sm tracking-wider ${estado.terminado ? 'text-hazard' : 'text-rust'}`}
      >
        {estado.texto}
      </span>
    </div>
  );
}
