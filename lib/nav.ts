import { cookies } from 'next/headers';
import { getDb } from './db';
import { getSession, esSuperadmin, esJunta, SessionPayload } from './session';

export interface NavContext {
  session: SessionPayload | null;
  esSuperadmin: boolean;
  esJunta: boolean;
  pendientesTotal: number;
  hayActasNuevas: boolean;
}

/** Reúne todo lo que la cabecera/menús necesitan, equivalente a los bloques
 * repetidos al principio de cada .php (sesión, pendientes de aprobar, aviso de actas). */
export async function getNavContext(): Promise<NavContext> {
  const session = await getSession();
  const db = await getDb();

  const junta = esJunta(session);
  const superadmin = esSuperadmin(session);

  let pendientesTotal = 0;
  if (junta) {
    pendientesTotal = await db.collection('usuarios').countDocuments({ aprobado: 0 });
  }

  let hayActasNuevas = false;
  if (session) {
    const config = await db.collection('configuracion').findOne({ clave: 'ultima_acta' });
    if (config?.valor) {
      const ultimaActa = new Date(config.valor).getTime();
      const cookieStore = await cookies();
      const ultimaVisita = Number(cookieStore.get('ultima_visita_actas')?.value || 0);
      hayActasNuevas = ultimaActa > ultimaVisita;
    }
  }

  return { session, esSuperadmin: superadmin, esJunta: junta, pendientesTotal, hayActasNuevas };
}
