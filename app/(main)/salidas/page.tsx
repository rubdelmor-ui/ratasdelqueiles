import Link from 'next/link';
import { requireSession, esSuperadmin } from '@/lib/session';
import { getSalidasConDatos, getSalidasPasadas } from '@/lib/salidas';
import SalidaCard from '@/components/salidas/SalidaCard';
import PageHeader from '@/components/PageHeader';

export default async function SalidasPage() {
  const session = await requireSession('/login');
  const superadmin = esSuperadmin(session);
  const [salidas, pasadas] = await Promise.all([getSalidasConDatos(session.id), getSalidasPasadas()]);

  return (
    <>
      <PageHeader
        eyebrow="Temporada activa"
        title="Próximas Salidas"
        subtitle="Rutas programadas. Asfalto, gasolina y hermandad."
        action={
          superadmin && (
            <Link href="/salidas/nueva" className="btn btn-primary btn-sm flex-shrink-0">
              <span className="material-symbols-outlined text-[18px]">add</span>
              Nueva
            </Link>
          )
        }
      />

      {salidas.length > 0 ? (
        <div className="flex flex-col gap-4 mt-1">
          {salidas.map((salida) => (
            <SalidaCard key={salida.id} salida={salida} sesionActiva={!!session} esSuperadmin={superadmin} />
          ))}
        </div>
      ) : (
        <div className="cut-panel bg-surface border border-steel/50 p-10 text-center mt-2">
          <span className="material-symbols-outlined text-6xl text-ash">motorcycle</span>
          <h3 className="display-text text-lg text-chrome mt-3">No hay salidas programadas</h3>
          <p className="text-smoke mt-1 text-sm">Próximamente publicaremos nuevas rutas.</p>
        </div>
      )}

      {pasadas.length > 0 && (
        <section className="mt-4">
          <span className="eyebrow flex items-center gap-2 mb-2">
            <span className="w-4 h-[2px] bg-rust" /> Salidas anteriores
          </span>
          <div className="flex flex-col gap-2">
            {pasadas.map((s) => (
              <Link
                key={s.id}
                href={`/salidas/${s.id}/fotos`}
                className="flex items-center gap-3 bg-surface border border-steel/50 rounded-lg px-3 py-3 hover:border-rust/60 transition-colors"
              >
                <span className="gauge-number text-xs text-smoke flex-shrink-0">
                  {new Date(`${s.fechaSalida}T00:00`).toLocaleDateString('es-ES')}
                </span>
                <span className="text-chrome font-medium flex-1 truncate">{s.destino}</span>
                <span className="badge badge-info flex-shrink-0">
                  <span className="material-symbols-outlined text-[12px]">photo_camera</span>
                  {s.totalFotos}
                </span>
              </Link>
            ))}
          </div>
        </section>
      )}
    </>
  );
}
