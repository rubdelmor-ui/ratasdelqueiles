'use client'

import { useState } from 'react';
import { createPortal } from 'react-dom';
import Image from 'next/image';

export default function ImagenExpandible({
  src,
  alt = '',
  className,
  sizes,
}: {
  src: string;
  alt?: string;
  className?: string;
  sizes?: string;
}) {
  const [abierta, setAbierta] = useState(false);

  return (
    <>
      <button type="button" onClick={() => setAbierta(true)} className={className}>
        <Image src={src} alt={alt} width={1200} height={1200} sizes={sizes} className="w-full h-full object-contain" />
      </button>

      {abierta &&
        createPortal(
          <div
            className="fixed inset-0 z-[1000] bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 cursor-zoom-out"
            onClick={() => setAbierta(false)}
          >
            <Image
              src={src}
              alt={alt}
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
