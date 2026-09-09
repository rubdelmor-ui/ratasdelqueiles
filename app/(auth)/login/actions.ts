'use server'

import { getDb } from '@/lib/db';
import bcrypt from 'bcryptjs';
import { cookies } from 'next/headers';
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

  const cookieStore = await cookies();
  cookieStore.set('auth_token', token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 7,
    path: '/',
  });

  return { success: true };
}
