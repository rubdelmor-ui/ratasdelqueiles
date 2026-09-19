import Link from 'next/link';
import { getDb } from '@/lib/db';
import { requireSession, esSuperadmin } from '@/lib/session';
import type { FotoSalida } from '@/lib/types';
import PageHeader from '@/components/PageHeader';
import ImagenExpandible from '@/components/ImagenExpandible';
import ConfirmSubmitButton from '@/components/ConfirmSubmitButton';
import SubirFotos from '@/components/salidas/SubirFotos';
import { borrarFoto } from './actions';

export default async function FotosSalidasPage() {
  const session = await requireSession('/login');
  const superadmin = esSuperadmin(session);

  const db = await getDb();
  const fotos = await db
    .collection<FotoSalida>('fotos_salidas')
    .find({})
    .sort({ fecha_subida: -1 })
    .toArray();

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Galería"
        title="Fotos de las salidas"
        subtitle="Cuelga tus fotos de las rutas para que las vea todo el club."
        action={
          <Link href="/salidas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <SubirFotos />

      {fotos.length === 0 ? (
        <div className="cut-panel bg-surface border border-steel/50 p-10 text-center">
          <span className="material-symbols-outlined text-6xl text-ash">photo_library</span>
          <p className="text-smoke mt-3">Todavía no hay fotos. ¡Sé el primero en subir una!</p>
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
          {fotos.map((foto) => {
            const id = foto._id.toString();
            return (
              <div key={id} className="rounded-lg border border-steel/50 bg-surface overflow-hidden">
                <ImagenExpandible
                  src={foto.url}
                  alt={`Foto de ${foto.usuario_nombre}`}
                  sizes="(max-width: 768px) 50vw, 300px"
                  className="block w-full aspect-square bg-surface-high"
                />
                <div className="flex items-center justify-between gap-2 px-2.5 py-2">
                  <div className="min-w-0">
                    <div className="text-chrome text-xs font-medium truncate">{foto.usuario_nombre}</div>
                    <div className="text-ash text-[10px] font-mono">
                      {new Date(foto.fecha_subida).toLocaleDateString('es-ES')}
                    </div>
                  </div>
                  {superadmin && (
                    <form action={borrarFoto.bind(null, id)}>
                      <ConfirmSubmitButton
                        confirmMessage="¿Eliminar esta foto?"
                        className="text-smoke hover:text-ember flex-shrink-0"
                      >
                        <span className="material-symbols-outlined text-[19px]">delete</span>
                      </ConfirmSubmitButton>
                    </form>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
