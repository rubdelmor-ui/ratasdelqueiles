import Link from 'next/link';
import { requireSuperadmin } from '@/lib/session';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';
import PageHeader from '@/components/PageHeader';
import { crearSalida } from './actions';

export default async function NuevaSalidaPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  await requireSuperadmin('/salidas');
  const { error } = await searchParams;
  const hoy = new Date().toISOString().slice(0, 10);

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Panel de junta"
        title="Nueva Salida"
        subtitle="Crea una nueva ruta para los socios."
        action={
          <Link href="/salidas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        {error && <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded mb-4 text-sm">{decodeURIComponent(error)}</div>}

        <form action={crearSalida} className="space-y-4">
          <div>
            <label className={labelDarkClass}>Destino *</label>
            <input type="text" name="destino" placeholder="Ej: Sierra Nevada" required className={inputDarkClass} />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className={labelDarkClass}>Fecha *</label>
              <input type="date" name="fecha_salida" defaultValue={hoy} required className={inputDarkClass} />
            </div>
            <div>
              <label className={labelDarkClass}>Hora *</label>
              <input type="time" name="hora_quedada" defaultValue="09:00" required className={inputDarkClass} />
            </div>
          </div>
          <div>
            <label className={labelDarkClass}>Punto de encuentro</label>
            <input type="text" name="punto_encuentro" placeholder="Ej: Gasolinera Norte" className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Descripción / Ruta</label>
            <textarea name="descripcion" rows={4} placeholder="Detalles de la ruta, paradas..." className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Responsable de la salida</label>
            <input type="text" name="responsable" placeholder="Ej: Juan Pérez" className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Imagen de la salida (opcional)</label>
            <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" className={`${inputDarkClass} py-2`} />
            <p className="text-ash text-xs mt-1">Formatos permitidos: JPG, PNG, WEBP.</p>
          </div>
          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">check_circle</span>
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
