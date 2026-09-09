'use client'

import { useState } from 'react';

export default function ToggleListado({ children }: { children: React.ReactNode }) {
  const [visible, setVisible] = useState(false);

  return (
    <>
      <button onClick={() => setVisible((v) => !v)} className="btn btn-ghost btn-sm">
        <span className="material-symbols-outlined text-[16px]">{visible ? 'expand_less' : 'expand_more'}</span>
        {visible ? 'Ocultar listado' : 'Ver listado'}
      </button>
      {visible && <div className="mt-4 w-full">{children}</div>}
    </>
  );
}
