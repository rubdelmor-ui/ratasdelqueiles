'use client'

import { useState } from 'react';
import Link from 'next/link';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';

export default function ApuntarseForm({
  action,
}: {
  action: (formData: FormData) => void;
}) {
  const [conAcompanantes, setConAcompanantes] = useState(false);
  const [numAcompanantes, setNumAcompanantes] = useState(1);

  return (
    <form action={action}>
      <p className={labelDarkClass}>¿Vienes con acompañantes?</p>
      <div className="flex gap-3">
        <label
          className={`flex-1 flex items-center justify-center gap-2 py-3 rounded-md border-2 cursor-pointer transition-colors ${
            !conAcompanantes ? 'border-rust bg-rust/10 text-chrome' : 'border-steel/50 text-smoke'
          }`}
        >
          <input
            type="radio"
            name="acompanantes"
            value="0"
            checked={!conAcompanantes}
            onChange={() => setConAcompanantes(false)}
            className="sr-only"
          />
          No
        </label>
        <label
          className={`flex-1 flex items-center justify-center gap-2 py-3 rounded-md border-2 cursor-pointer transition-colors ${
            conAcompanantes ? 'border-rust bg-rust/10 text-chrome' : 'border-steel/50 text-smoke'
          }`}
        >
          <input
            type="radio"
            name="acompanantes"
            value="1"
            checked={conAcompanantes}
            onChange={() => setConAcompanantes(true)}
            className="sr-only"
          />
          Sí
        </label>
      </div>

      {conAcompanantes && (
        <div className="mt-5">
          <label className={labelDarkClass}>Número de acompañantes</label>
          <select
            value={numAcompanantes}
            onChange={(e) => setNumAcompanantes(Number(e.target.value))}
            className={inputDarkClass}
          >
            {[1, 2, 3, 4, 5].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
          <div className="mt-3 space-y-3">
            {Array.from({ length: numAcompanantes }).map((_, i) => (
              <div key={i}>
                <label className={labelDarkClass}>Nombre acompañante {i + 1}</label>
                <input type="text" name="acompanante" required placeholder="Nombre del acompañante" className={inputDarkClass} />
              </div>
            ))}
          </div>
        </div>
      )}

      <button type="submit" className="btn btn-primary btn-block mt-6">
        <span className="material-symbols-outlined">check_circle</span> Apuntarse
      </button>
      <Link href="/salidas" className="block text-center text-smoke hover:text-rust transition-colors mt-4 text-xs font-mono uppercase tracking-wide">
        ← Volver a salidas
      </Link>
    </form>
  );
}
