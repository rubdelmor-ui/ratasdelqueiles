'use server'

import { getDb } from '@/lib/db';
import bcrypt from 'bcryptjs';
import { cookies, headers } from 'next/headers';
import { createSessionToken } from '@/lib/session';

export async function loginUsuario(formData: FormData) {
  const email = formData.get('email') as string;
  const password = formData.get('password') as string;

  const db = await getDb();
  const user = await db.collection('usuarios').findOne({ email });
  if (!user) {
    return { error: '❌ No existe un usuario con ese correo electrónico.' };
  }

  const isValid = await bcrypt.compare(password, user.password);
  if (!isValid) {
    return { error: '❌ Contraseña incorrecta.' };
  }

  if (user.aprobado !== 1) {
    return { error: '⏳ Tu cuenta está pendiente de aprobación por la Junta Directiva.' };
  }

  const token = await createSessionToken({
    id: user._id.toString(),
    nombre: user.nombre,
    email: user.email,
    rol: user.rol,
  });

  // En Vercel llega por HTTPS (x-forwarded-proto lo confirma); en el
  // despliegue Docker propio se sirve por HTTP plano dentro del tailnet,
  // así que `secure: NODE_ENV === 'production'` descartaba la cookie ahí.
  const isHttps = (await headers()).get('x-forwarded-proto') === 'https';

  const cookieStore = await cookies();
  cookieStore.set('auth_token', token, {
    httpOnly: true,
    secure: isHttps,
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 7,
    path: '/',
  });

  return { success: true };
}
