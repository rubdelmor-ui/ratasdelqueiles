import Link from 'next/link';
import { getDb } from '@/lib/db';
import { requireJunta, esSuperadmin } from '@/lib/session';
import type { Acta } from '@/lib/types';
import MarcarVisita from '@/components/actas/MarcarVisita';
import ConfirmSubmitButton from '@/components/ConfirmSubmitButton';
import PageHeader from '@/components/PageHeader';
import { borrarActa } from './actions';

export default async function ActasPage() {
  const session = await requireJunta('/');
  const superadmin = esSuperadmin(session);

  const db = await getDb();
  const actas = await db
    .collection<Acta>('actas')
    .find({})
    .sort({ fecha_reunion: -1 })
    .toArray();

  return (
    <>
      <MarcarVisita />
      <PageHeader
        eyebrow="Sala de juntas"
        title="Registro de Actas"
        subtitle="Historial oficial de asambleas, acuerdos y resoluciones."
        action={
          superadmin && (
            <Link href="/actas/nueva" className="btn btn-primary btn-sm flex-shrink-0">
              <span className="material-symbols-outlined text-[18px]">add</span>
              Nueva
            </Link>
          )
        }
      />

      <div className="flex flex-col gap-3 mt-1">
        {actas.length === 0 && (
          <div className="cut-panel bg-surface border border-steel/50 p-10 text-center">
            <span className="material-symbols-outlined text-6xl text-ash">description</span>
            <p className="text-smoke mt-3">No hay actas registradas todavía.</p>
          </div>
        )}
        {actas.map((acta) => {
          const borrarConId = borrarActa.bind(null, acta._id.toString());
          return (
            <div key={acta._id.toString()} className="cut-panel-sm bg-surface border border-steel/50 p-4 flex gap-3">
              <div className="flex flex-col items-center justify-center bg-surface-high rounded px-3 py-2 border border-steel/40 min-w-[64px]">
                <span className="material-symbols-outlined text-rust">description</span>
                <span className="gauge-number text-[10px] text-smoke mt-1 text-center">{acta.fecha_reunion}</span>
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="display-text text-base text-chrome leading-tight">{acta.titulo}</h3>
                <span className="skew-chip bg-surface-higher border-steel/60 mt-1.5">
                  <span className="skew-chip-inner text-smoke text-[10px] px-2 py-0.5">
                    ✍️ {acta.firmas} firmas
                  </span>
                </span>
                <div className="flex gap-3 items-center mt-2.5">
                  {acta.archivo_pdf && (
                    <a href={acta.archivo_pdf} target="_blank" rel="noreferrer" className="btn btn-danger btn-sm">
                      <span className="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                      Ver PDF
                    </a>
                  )}
                  {superadmin && (
                    <>
                      <Link href={`/actas/${acta._id.toString()}/editar`} className="text-smoke hover:text-rust">
                        <span className="material-symbols-outlined text-[19px]">edit</span>
                      </Link>
                      <form action={borrarConId}>
                        <ConfirmSubmitButton confirmMessage="¿Borrar esta acta?" className="text-smoke hover:text-ember">
                          <span className="material-symbols-outlined text-[19px]">delete</span>
                        </ConfirmSubmitButton>
                      </form>
                    </>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </>
  );
}
