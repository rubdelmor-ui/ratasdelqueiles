import Link from 'next/link';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import type { ContenidoHome } from '@/lib/types';
import PageHeader from '@/components/PageHeader';
import { guardarHome } from './actions';

export default async function EditarHomePage({
  searchParams,
}: {
  searchParams: Promise<{ ok?: string; error?: string }>;
}) {
  await requireSuperadmin('/');
  const { ok, error } = await searchParams;

  const db = await getDb();
  const contenido = await db
    .collection<ContenidoHome>('contenido_home')
    .findOne({ seccion: 'bienvenida' });

  const mensajesError: Record<string, string> = {
    formato: 'Formato de imagen no permitido.',
    cloudinary: 'Error al subir la imagen a la nube.',
  };

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Panel de superadmin"
        title="Editar Home"
        subtitle="Modifica el texto de bienvenida y la imagen principal."
        action={
          <Link href="/" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        {ok && <div className="bg-moss/15 border border-moss/40 text-chrome rounded-lg px-4 py-3 mb-5 text-sm">✅ Contenido actualizado correctamente.</div>}
        {error && <div className="bg-ember/15 border border-ember/40 text-flame rounded-lg px-4 py-3 mb-5 text-sm">{mensajesError[error] || 'Ha ocurrido un error.'}</div>}

        <form action={guardarHome} className="space-y-4">
          <div>
            <label className="field-label">Texto de bienvenida</label>
            <textarea name="contenido" rows={5} defaultValue={contenido?.contenido || ''} className="field-input" />
          </div>

          <div>
            <label className="field-label">Texto destacado sobre la imagen (opcional)</label>
            <textarea name="texto_imagen" rows={2} defaultValue={contenido?.texto_imagen || ''} className="field-input" />
          </div>

          <div className="cut-panel-sm bg-surface-high p-4 border border-steel/40">
            <label className="field-label text-rust">Imagen actual</label>
            {contenido?.imagen ? (
              <img src={contenido.imagen} className="max-w-[200px] max-h-[200px] rounded border border-steel/50" alt="Imagen actual" />
            ) : (
              <p className="text-sm text-smoke">No hay imagen en la nube.</p>
            )}
          </div>

          <div>
            <label className="field-label">Subir nueva imagen (opcional)</label>
            <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.gif,.webp" className="field-input py-2" />
          </div>

          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">save</span>
              Guardar
            </button>
            <Link href="/" className="btn btn-ghost flex-1">
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
