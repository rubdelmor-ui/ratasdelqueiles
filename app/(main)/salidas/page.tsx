import Link from 'next/link';
import { requireSession, esSuperadmin } from '@/lib/session';
import { getSalidasConDatos } from '@/lib/salidas';
import SalidaCard from '@/components/salidas/SalidaCard';
import PageHeader from '@/components/PageHeader';

export default async function SalidasPage() {
  const session = await requireSession('/login');
  const superadmin = esSuperadmin(session);
  const salidas = await getSalidasConDatos(session.id);

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
    </>
  );
}
