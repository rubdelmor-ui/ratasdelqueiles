'use client'

import { useRef, useState } from 'react';
import { useRouter } from 'next/navigation';
import { subirFoto } from '@/app/(main)/salidas/[id]/fotos/actions';

const MAX_LADO = 1600;
// Vercel rechaza cuerpos de petición de más de ~4,5 MB.
const MAX_BYTES = 4 * 1024 * 1024;

function cargarImagen(file: File): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
      URL.revokeObjectURL(url);
      resolve(img);
    };
    img.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('No se pudo leer la imagen'));
    };
    img.src = url;
  });
}

/** Reduce la foto a JPEG de como mucho 1600px para que quepa en una petición. */
async function comprimir(file: File): Promise<File> {
  try {
    const img = await cargarImagen(file);
    const escala = Math.min(1, MAX_LADO / Math.max(img.naturalWidth, img.naturalHeight));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(img.naturalWidth * escala);
    canvas.height = Math.round(img.naturalHeight * escala);
    canvas.getContext('2d')?.drawImage(img, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise<Blob | null>((resolve) =>
      canvas.toBlob(resolve, 'image/jpeg', 0.82)
    );
    if (!blob) return file;
    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
  } catch {
    return file;
  }
}

export default function SubirFotos({ salidaId }: { salidaId: string }) {
  const router = useRouter();
  const inputRef = useRef<HTMLInputElement>(null);
  const [progreso, setProgreso] = useState<{ hecho: number; total: number } | null>(null);
  const [errores, setErrores] = useState<string[]>([]);
  const [subidas, setSubidas] = useState(0);

  async function onChange(e: React.ChangeEvent<HTMLInputElement>) {
    const archivos = Array.from(e.target.files ?? []);
    if (archivos.length === 0) return;

    setErrores([]);
    setSubidas(0);
    setProgreso({ hecho: 0, total: archivos.length });
    const fallos: string[] = [];

    for (let i = 0; i < archivos.length; i++) {
      const original = archivos[i];
      const foto = await comprimir(original);

      if (foto.size > MAX_BYTES) {
        fallos.push(`${original.name}: la foto es demasiado grande.`);
      } else {
        try {
          const formData = new FormData();
          formData.append('foto', foto);
          const res = await subirFoto(salidaId, formData);
          if (res.error) fallos.push(`${original.name}: ${res.error}`);
        } catch {
          fallos.push(`${original.name}: no se pudo subir.`);
        }
      }
      setProgreso({ hecho: i + 1, total: archivos.length });
    }

    if (inputRef.current) inputRef.current.value = '';
    setProgreso(null);
    setErrores(fallos);
    setSubidas(archivos.length - fallos.length);
    router.refresh();
  }

  return (
    <div>
      <label className={`btn btn-primary btn-block ${progreso ? 'opacity-60 pointer-events-none' : 'cursor-pointer'}`}>
        <span className="material-symbols-outlined text-[20px]">add_a_photo</span>
        {progreso ? `Subiendo ${progreso.hecho}/${progreso.total}…` : 'Subir fotos'}
        <input
          ref={inputRef}
          type="file"
          accept="image/*"
          multiple
          disabled={!!progreso}
          onChange={onChange}
          className="sr-only"
        />
      </label>

      {subidas > 0 && (
        <div className="bg-moss/15 border border-moss/40 text-chrome p-3 rounded mt-3 text-sm">
          ✅ {subidas === 1 ? 'Foto subida' : `${subidas} fotos subidas`} correctamente.
        </div>
      )}

      {errores.length > 0 && (
        <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded mt-3 text-sm space-y-1">
          {errores.map((msg, i) => (
            <p key={i}>{msg}</p>
          ))}
        </div>
      )}
    </div>
  );
}
