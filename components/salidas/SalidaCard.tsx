import Link from 'next/link';
import type { SalidaConDatos } from '@/lib/salidas';
import Countdown from './Countdown';
import AsistentesButton from './AsistentesButton';
import { toggleApuntarse, borrarSalida } from '@/app/(main)/salidas/actions';
import ConfirmSubmitButton from '@/components/ConfirmSubmitButton';

function formatearFechaHora(fecha: string, hora: string) {
  const dt = new Date(`${fecha}T${hora}`);
  const dia = dt.toLocaleDateString('es-ES', { day: '2-digit' });
  const mes = dt.toLocaleDateString('es-ES', { month: 'short' }).replace('.', '');
  return { dia, mes, hora };
}

export default function SalidaCard({
  salida,
  sesionActiva,
  esSuperadmin,
}: {
  salida: SalidaConDatos;
  sesionActiva: boolean;
  esSuperadmin: boolean;
}) {
  const { dia, mes, hora } = formatearFechaHora(salida.fechaSalida, salida.horaQuedada);

  async function toggle() {
    'use server'
    await toggleApuntarse(salida.id);
  }

  async function borrar() {
    'use server'
    await borrarSalida(salida.id);
  }

  return (
    <article className="cut-panel relative bg-surface border border-steel/50 flex overflow-hidden">
      <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-rust" />

      {/* Bloque fecha estilo "salida de vuelos" */}
      <div className="flex flex-col items-center justify-center bg-surface-high px-4 py-4 border-r border-dashed border-steel/50 min-w-[76px]">
        <span className="display-text text-3xl text-rust leading-none">{dia}</span>
        <span className="eyebrow mt-1">{mes}</span>
        <span className="gauge-number text-xs text-smoke mt-2">{hora}</span>
      </div>

      <div className="flex-1 p-4 flex flex-col gap-2.5 min-w-0">
        <div className="flex items-start justify-between gap-2">
          <h4 className="display-text text-lg text-chrome leading-tight">{salida.destino}</h4>
          {salida.imagen && (
            <img src={salida.imagen} alt="" className="w-12 h-12 rounded object-cover border border-steel/50 flex-shrink-0" />
          )}
        </div>

        {salida.puntoEncuentro && (
          <div className="flex items-center gap-1.5 text-smoke text-sm">
            <span className="material-symbols-outlined text-[16px]">location_on</span>
            {salida.puntoEncuentro}
          </div>
        )}

        {salida.descripcion && <p className="text-smoke text-sm line-clamp-2">{salida.descripcion}</p>}

        <div className="flex items-center flex-wrap gap-2 mt-1">
          <Countdown fechaIso={salida.fechaIso} />
        </div>

        <div className="flex items-center flex-wrap gap-2">
          {salida.responsable && (
            <span className="badge badge-info">
              <span className="material-symbols-outlined text-[12px]">badge</span>
              {salida.responsable}
            </span>
          )}
          <AsistentesButton
            salidaId={salida.id}
            total={salida.totalInscritos}
            asistentes={salida.listaAsistentes}
            esSuperadmin={esSuperadmin}
          />
        </div>

        <div className="flex items-center justify-between border-t border-dashed border-steel/40 pt-3 mt-1">
          {sesionActiva ? (
            salida.yaApuntado ? (
              <form action={toggle}>
                <button type="submit" className="text-ember font-mono text-xs uppercase tracking-wide hover:underline">
                  ✕ Ya no voy
                </button>
              </form>
            ) : (
              <Link href={`/salidas/${salida.id}/apuntarse`} className="btn btn-primary btn-sm">
                <span className="material-symbols-outlined text-[16px]">add_road</span>
                Apuntarse
              </Link>
            )
          ) : (
            <span className="text-ash font-mono text-xs uppercase">🔒 Inicia sesión</span>
          )}

          {esSuperadmin && (
            <div className="flex gap-3 items-center">
              <Link href={`/salidas/${salida.id}/editar`} className="text-smoke hover:text-rust">
                <span className="material-symbols-outlined text-[19px]">edit</span>
              </Link>
              <form action={borrar}>
                <ConfirmSubmitButton
                  confirmMessage="¿Seguro que quieres borrar esta salida?"
                  className="text-smoke hover:text-ember"
                >
                  <span className="material-symbols-outlined text-[19px]">delete</span>
                </ConfirmSubmitButton>
              </form>
            </div>
          )}
        </div>
      </div>
    </article>
  );
}
