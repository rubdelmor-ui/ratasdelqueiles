import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';
import PageHeader from '@/components/PageHeader';
import { actualizarActa } from './actions';

export default async function EditarActaPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ error?: string }>;
}) {
  await requireSuperadmin('/actas');
  const { id } = await params;
  const { error } = await searchParams;

  const db = await getDb();
  const acta = await db.collection('actas').findOne({ _id: new ObjectId(id) });
  if (!acta) notFound();

  const actualizarConId = actualizarActa.bind(null, id);

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Sala de juntas"
        title="Editar Acta"
        action={
          <Link href="/actas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      {error && <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded text-sm">{decodeURIComponent(error)}</div>}

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        <form action={actualizarConId} className="space-y-4">
          <input type="hidden" name="archivo_antiguo" value={acta.archivo_pdf || ''} />

          <div>
            <label className={labelDarkClass}>Título *</label>
            <input type="text" name="titulo" defaultValue={acta.titulo} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Fecha *</label>
            <input type="date" name="fecha_reunion" defaultValue={acta.fecha_reunion} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Número de asistentes (firmas) *</label>
            <input type="number" name="asistentes" defaultValue={acta.firmas} min={0} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Autor</label>
            <input type="text" name="autor" defaultValue={acta.autor} className={inputDarkClass} />
          </div>

          <div className="cut-panel-sm bg-surface-high p-4 border border-steel/40">
            <label className={`${labelDarkClass} text-rust`}>PDF actual</label>
            {acta.archivo_pdf ? (
              <p className="text-sm truncate text-chrome">📄 Guardado en la nube</p>
            ) : (
              <p className="text-sm text-ash">No hay PDF asociado.</p>
            )}
          </div>

          <div>
            <label className={labelDarkClass}>Reemplazar PDF (opcional)</label>
            <input type="file" name="archivo_pdf" accept=".pdf" className={`${inputDarkClass} py-2`} />
          </div>

          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">save</span>
              Actualizar
            </button>
            <Link href="/actas" className="btn btn-ghost flex-1">
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
