import Link from 'next/link';
import { requireSuperadmin } from '@/lib/session';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';
import PageHeader from '@/components/PageHeader';
import { crearActa } from './actions';

export default async function NuevaActaPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  await requireSuperadmin('/actas');
  const { error } = await searchParams;
  const hoy = new Date().toISOString().slice(0, 10);

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Sala de juntas"
        title="Nueva Acta"
        action={
          <Link href="/actas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      {error && <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded text-sm">{decodeURIComponent(error)}</div>}

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        <form action={crearActa} className="space-y-4">
          <div>
            <label className={labelDarkClass}>Título *</label>
            <input type="text" name="titulo" placeholder="Ej: Acta Asamblea General 2026" required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Fecha de reunión *</label>
            <input type="date" name="fecha_reunion" defaultValue={hoy} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Número de asistentes (firmas) *</label>
            <input type="number" name="asistentes" min={0} defaultValue={0} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Autor / Secretario</label>
            <input type="text" name="autor" placeholder="Quien redacta el acta" className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Subir PDF (opcional pero recomendado)</label>
            <input type="file" name="archivo_pdf" accept=".pdf" className={`${inputDarkClass} py-2`} />
          </div>
          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">check_circle</span>
              Guardar
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
