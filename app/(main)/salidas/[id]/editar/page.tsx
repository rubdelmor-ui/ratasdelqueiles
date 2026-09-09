import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';
import PageHeader from '@/components/PageHeader';
import { actualizarSalida } from './actions';

export default async function EditarSalidaPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  await requireSuperadmin('/salidas');
  const { id } = await params;
  const { error } = await searchParams;

  const db = await getDb();
  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(id) });
  if (!salida) notFound();

  const actualizarConId = actualizarSalida.bind(null, id);

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Panel de junta"
        title="Editar Salida"
        subtitle="Modifica los datos de la salida."
        action={
          <Link href="/salidas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        {error && <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded mb-4">{decodeURIComponent(error)}</div>}

        <form action={actualizarConId} className="space-y-4">
          <input type="hidden" name="imagen_antigua" value={salida.imagen || ''} />

          <div>
            <label className={labelDarkClass}>Destino *</label>
            <input type="text" name="destino" defaultValue={salida.destino} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Fecha *</label>
            <input type="date" name="fecha_salida" defaultValue={salida.fecha_salida} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Hora *</label>
            <input type="time" name="hora_quedada" defaultValue={salida.hora_quedada} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Punto de encuentro</label>
            <input type="text" name="punto_encuentro" defaultValue={salida.punto_encuentro} className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Descripción</label>
            <textarea name="descripcion" rows={5} defaultValue={salida.descripcion} className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Responsable de la salida</label>
            <input type="text" name="responsable" defaultValue={salida.responsable} className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Imagen actual</label>
            {salida.imagen ? (
              <img src={salida.imagen} className="max-w-[150px] max-h-[150px] rounded border border-steel/50" alt="Imagen actual" />
            ) : (
              <p className="text-smoke text-sm">No hay imagen en la nube.</p>
            )}
          </div>
          <div>
            <label className={labelDarkClass}>Cambiar imagen (opcional)</label>
            <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" className={`${inputDarkClass} py-2`} />
          </div>
          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">save</span>
              Guardar
            </button>
            <Link href="/salidas" className="btn btn-ghost flex-1">
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
