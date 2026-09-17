'use client'

import { useState } from 'react';
import { createPortal } from 'react-dom';
import Image from 'next/image';

export default function SalidaImagen({ src }: { src: string }) {
  const [abierta, setAbierta] = useState(false);

  return (
    <>
      <button
        type="button"
        onClick={() => setAbierta(true)}
        className="w-full h-44 rounded-lg border border-steel/50 bg-surface-high overflow-hidden"
      >
        <Image src={src} alt="" width={480} height={220} className="w-full h-full object-contain" />
      </button>

      {abierta &&
        createPortal(
          <div
            className="fixed inset-0 z-[1000] bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 cursor-zoom-out"
            onClick={() => setAbierta(false)}
          >
            <Image
              src={src}
              alt=""
              width={1200}
              height={1200}
              className="max-w-full max-h-full w-auto h-auto object-contain"
            />
          </div>,
          document.body
        )}
    </>
  );
}
