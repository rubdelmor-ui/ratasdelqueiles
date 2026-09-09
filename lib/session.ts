import { jwtVerify, SignJWT } from 'jose';
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';

const JWT_SECRET = new TextEncoder().encode(
  process.env.JWT_SECRET || 'clave_secreta_temporal_para_desarrollo_cambiala'
);

export const SUPERADMIN_EMAIL = process.env.SUPERADMIN_EMAIL || 'admin@club.com';

export type Rol = 'socio' | 'junta';

export interface SessionPayload {
  id: string;
  nombre: string;
  email: string;
  rol: Rol;
}

export async function createSessionToken(payload: SessionPayload) {
  return new SignJWT({ ...payload })
    .setProtectedHeader({ alg: 'HS256' })
    .setIssuedAt()
    .setExpirationTime('7d')
    .sign(JWT_SECRET);
}

export async function getSession(): Promise<SessionPayload | null> {
  const cookieStore = await cookies();
  const token = cookieStore.get('auth_token')?.value;
  if (!token) return null;

  try {
    const { payload } = await jwtVerify(token, JWT_SECRET);
    return {
      id: payload.id as string,
      nombre: payload.nombre as string,
      email: payload.email as string,
      rol: payload.rol as Rol,
    };
  } catch {
    return null;
  }
}

export function esSuperadmin(session: SessionPayload | null): boolean {
  return !!session && session.email === SUPERADMIN_EMAIL;
}

export function esJunta(session: SessionPayload | null): boolean {
  return !!session && session.rol === 'junta';
}

/** Redirige si no hay sesión activa. Igual que el `if (!isset($_SESSION['usuario_id']))` del PHP. */
export async function requireSession(redirectTo = '/login'): Promise<SessionPayload> {
  const session = await getSession();
  if (!session) redirect(redirectTo);
  return session;
}

/** Redirige si el usuario no es de la junta. */
export async function requireJunta(redirectTo = '/'): Promise<SessionPayload> {
  const session = await getSession();
  if (!session || session.rol !== 'junta') redirect(redirectTo);
  return session;
}

/** Redirige si el usuario no es el superadmin (siempre junta + email superadmin). */
export async function requireSuperadmin(redirectTo = '/'): Promise<SessionPayload> {
  const session = await getSession();
  if (!session || session.rol !== 'junta' || session.email !== SUPERADMIN_EMAIL) {
    redirect(redirectTo);
  }
  return session;
}
