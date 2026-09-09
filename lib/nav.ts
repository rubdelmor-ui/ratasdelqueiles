import { cookies } from 'next/headers';
import { getDb } from './db';
import { getSession, esSuperadmin, esJunta, SessionPayload } from './session';

export interface NavContext {
  session: SessionPayload | null;
  esSuperadmin: boolean;
  esJunta: boolean;
  pendientesTotal: number;
  hayActasNuevas: boolean;
  reunionTexto: string;
  reunionLugar: string;
}

/** Reúne todo lo que la cabecera/menús necesitan, equivalente a los bloques
 * repetidos al principio de cada .php (sesión, pendientes de aprobar, aviso de actas). */
export async function getNavContext(): Promise<NavContext> {
  const session = await getSession();
  const db = await getDb();

  const junta = esJunta(session);
  const superadmin = esSuperadmin(session);

  const [pendientesTotal, config, reunionConfig] = await Promise.all([
    junta ? db.collection('usuarios').countDocuments({ aprobado: 0 }) : Promise.resolve(0),
    session ? db.collection('configuracion').findOne({ clave: 'ultima_acta' }) : Promise.resolve(null),
    db
      .collection('configuracion')
      .find({ clave: { $in: ['reunion_texto', 'reunion_lugar'] } })
      .toArray(),
  ]);

  let hayActasNuevas = false;
  if (config?.valor) {
    const ultimaActa = new Date(config.valor).getTime();
    const cookieStore = await cookies();
    const ultimaVisita = Number(cookieStore.get('ultima_visita_actas')?.value || 0);
    hayActasNuevas = ultimaActa > ultimaVisita;
  }

  const reunionTexto = reunionConfig.find((c) => c.clave === 'reunion_texto')?.valor || 'Viernes · 20:00';
  const reunionLugar = reunionConfig.find((c) => c.clave === 'reunion_lugar')?.valor || 'Sede del Club';

  return { session, esSuperadmin: superadmin, esJunta: junta, pendientesTotal, hayActasNuevas, reunionTexto, reunionLugar };
}
