import Link from 'next/link';
import { getDb } from '@/lib/db';
import { requireJunta, esSuperadmin } from '@/lib/session';
import type { Usuario } from '@/lib/types';
import AvatarSocio from '@/components/socios/AvatarSocio';
import ToggleListado from '@/components/socios/ToggleListado';
import ConfirmSubmitButton from '@/components/ConfirmSubmitButton';
import PageHeader from '@/components/PageHeader';
import { aprobarSocio, rechazarSocio, hacerJunta, quitarJunta, eliminarSocio } from './actions';

export default async function SociosPage() {
  const session = await requireJunta('/');
  const superadmin = esSuperadmin(session);

  const db = await getDb();
  const usuarios = await db.collection<Usuario>('usuarios').find({}).toArray();

  const pendientes = usuarios
    .filter((u) => u.aprobado === 0)
    .sort((a, b) => a.fecha_registro.getTime() - b.fecha_registro.getTime());
  const aprobados = usuarios.filter((u) => u.aprobado === 1).sort((a, b) => a.nombre.localeCompare(b.nombre));
  const todos = [...usuarios].sort((a, b) => b.fecha_registro.getTime() - a.fecha_registro.getTime());

  return (
    <>
      <PageHeader
        eyebrow="Panel de junta"
        title="Gestión de Socios"
        subtitle="Aprueba solicitudes y gestiona roles del club."
        action={
          superadmin && (
            <span className="skew-chip bg-hazard flex-shrink-0">
              <span className="skew-chip-inner text-asphalt-low text-[10px] px-2.5 py-1">
                <span className="material-symbols-outlined text-[13px]">shield</span> Superadmin
              </span>
            </span>
          )
        }
      />

      <div className="cut-panel-sm bg-surface-high border border-steel/50 p-4 flex items-start gap-3 text-sm">
        <span className="material-symbols-outlined text-rust flex-shrink-0">{superadmin ? 'workspace_premium' : 'lock'}</span>
        <span className="text-smoke">
          {superadmin ? (
            <>
              <strong className="text-chrome">Superadmin:</strong> control total para ascender/descender, editar,
              eliminar socios y exportar el listado completo.
            </>
          ) : (
            <>
              <strong className="text-chrome">Miembro de la Junta:</strong> puedes aprobar/rechazar solicitudes, pero
              no cambiar roles, editar ni eliminar socios.
            </>
          )}
        </span>
      </div>

      {/* Pendientes */}
      <section>
        <div className="flex items-center gap-2 mb-3">
          <span className="material-symbols-outlined text-hazard text-[20px]">pending</span>
          <h3 className="eyebrow text-chrome">Solicitudes pendientes</h3>
          {pendientes.length > 0 && <span className="badge badge-pending">{pendientes.length}</span>}
        </div>

        {pendientes.length === 0 ? (
          <p className="text-smoke text-sm text-center py-4 cut-panel-sm bg-surface border border-steel/40">
            ✅ No hay solicitudes pendientes.
          </p>
        ) : (
          <div className="flex flex-col gap-3">
            {pendientes.map((socio) => {
              const id = socio._id.toString();
              const aprobar = aprobarSocio.bind(null, id);
              const rechazar = rechazarSocio.bind(null, id);
              return (
                <div key={id} className="cut-panel-sm bg-surface border border-steel/50 p-4">
                  <div className="flex items-center gap-3">
                    <AvatarSocio foto={socio.foto} />
                    <div className="min-w-0">
                      <div className="font-semibold text-chrome truncate">{socio.nombre}</div>
                      <div className="text-smoke text-xs truncate">{socio.email}</div>
                      <div className="text-ash text-[11px] font-mono mt-0.5">
                        {new Date(socio.fecha_registro).toLocaleDateString('es-ES')}
                      </div>
                    </div>
                  </div>
                  <div className="flex gap-2 flex-wrap mt-3">
                    <form action={aprobar}>
                      <button type="submit" className="btn btn-success btn-sm">
                        <span className="material-symbols-outlined text-[14px]">check</span> Aprobar
                      </button>
                    </form>
                    <form action={rechazar}>
                      <ConfirmSubmitButton confirmMessage="¿Rechazar esta solicitud?" className="btn btn-danger btn-sm">
                        <span className="material-symbols-outlined text-[14px]">close</span> Rechazar
                      </ConfirmSubmitButton>
                    </form>
                    {superadmin && (
                      <>
                        <Link href={`/socios/${id}/editar`} className="btn btn-ghost btn-sm">
                          <span className="material-symbols-outlined text-[14px]">edit</span>
                        </Link>
                        <form action={eliminarSocio.bind(null, id)}>
                          <ConfirmSubmitButton confirmMessage="¿Eliminar definitivamente a este socio?" className="btn btn-ghost btn-sm">
                            <span className="material-symbols-outlined text-[14px] text-ember">delete</span>
                          </ConfirmSubmitButton>
                        </form>
                      </>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </section>

      {/* Activos */}
      <section>
        <div className="flex items-center gap-2 mb-3">
          <span className="material-symbols-outlined text-rust text-[20px]">groups</span>
          <h3 className="eyebrow text-chrome">Socios activos</h3>
        </div>

        {aprobados.length === 0 ? (
          <p className="text-smoke text-sm text-center py-4">No hay socios activos.</p>
        ) : (
          <div className="flex flex-col gap-3">
            {aprobados.map((socio) => {
              const id = socio._id.toString();
              const esYo = id === session.id;
              return (
                <div key={id} className="cut-panel-sm bg-surface border border-steel/50 p-4">
                  <div className="flex items-center gap-3">
                    <AvatarSocio foto={socio.foto} />
                    <div className="min-w-0">
                      <div className="font-semibold text-chrome truncate">{socio.nombre}</div>
                      <div className="text-smoke text-xs truncate">{socio.email}</div>
                      <div className="flex gap-1.5 mt-1">
                        {socio.rol === 'junta' ? (
                          <>
                            <span className="badge badge-junta">Junta</span>
                            {socio.cargo && <span className="badge badge-info">{socio.cargo}</span>}
                          </>
                        ) : (
                          <span className="badge badge-socio">Socio</span>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="flex gap-2 flex-wrap items-center mt-3">
                    {esYo ? (
                      <>
                        {superadmin && (
                          <Link href={`/socios/${id}/editar`} className="btn btn-ghost btn-sm">
                            <span className="material-symbols-outlined text-[14px]">edit</span> Editar
                          </Link>
                        )}
                        <span className="text-ash text-xs italic">(Eres tú)</span>
                      </>
                    ) : superadmin ? (
                      <>
                        <Link href={`/socios/${id}/editar`} className="btn btn-ghost btn-sm">
                          <span className="material-symbols-outlined text-[14px]">edit</span>
                        </Link>
                        {socio.rol === 'socio' ? (
                          <form action={hacerJunta.bind(null, id)}>
                            <ConfirmSubmitButton confirmMessage="¿Ascender a este socio a JUNTA?" className="btn btn-warn btn-sm">
                              ⬆ Junta
                            </ConfirmSubmitButton>
                          </form>
                        ) : (
                          <form action={quitarJunta.bind(null, id)}>
                            <ConfirmSubmitButton confirmMessage="¿Quitar el rango de JUNTA?" className="btn btn-ghost btn-sm">
                              ⬇ Quitar junta
                            </ConfirmSubmitButton>
                          </form>
                        )}
                        <form action={eliminarSocio.bind(null, id)}>
                          <ConfirmSubmitButton confirmMessage="¿Eliminar definitivamente a este socio?" className="btn btn-danger btn-sm">
                            <span className="material-symbols-outlined text-[14px]">delete</span>
                          </ConfirmSubmitButton>
                        </form>
                      </>
                    ) : (
                      <span className="text-ash text-xs">🔒 Solo superadmin</span>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </section>

      {/* Listado completo */}
      {superadmin && (
        <section>
          <div className="flex items-center justify-between gap-3 mb-3 flex-wrap">
            <div className="flex items-center gap-2">
              <span className="material-symbols-outlined text-rust text-[20px]">list_alt</span>
              <h3 className="eyebrow text-chrome">Listado completo</h3>
            </div>
            <a href="/api/socios/csv" className="btn btn-success btn-sm">
              <span className="material-symbols-outlined text-[14px]">download</span> Excel
            </a>
          </div>

          <ToggleListado>
            <div className="flex flex-col gap-2">
              {todos.map((socio) => (
                <div key={socio._id.toString()} className="flex items-center gap-3 bg-surface border border-steel/40 rounded-lg px-3 py-2.5">
                  <AvatarSocio foto={socio.foto} size={36} />
                  <div className="min-w-0 flex-1">
                    <div className="text-chrome text-sm font-medium truncate">{socio.nombre}</div>
                    <div className="text-ash text-[11px] truncate">{socio.email}</div>
                  </div>
                  <div className="flex flex-col items-end gap-1 flex-shrink-0">
                    {socio.rol === 'junta' ? <span className="badge badge-junta">Junta</span> : <span className="badge badge-socio">Socio</span>}
                    {socio.aprobado === 1 ? (
                      <span className="text-moss text-[10px] font-mono">✅ Aprobado</span>
                    ) : (
                      <span className="badge badge-pending">Pendiente</span>
                    )}
                  </div>
                </div>
              ))}
            </div>
            <p className="text-ash text-xs mt-3 font-mono">Total: {todos.length}</p>
          </ToggleListado>
        </section>
      )}
    </>
  );
}
