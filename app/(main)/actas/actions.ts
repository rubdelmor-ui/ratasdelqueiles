'use server'

import { ObjectId } from 'mongodb';
import { cookies } from 'next/headers';
import { getDb } from '@/lib/db';
import { requireJunta, requireSuperadmin } from '@/lib/session';
import { revalidatePath } from 'next/cache';

/** Equivalente a `$_SESSION['ultima_visita_actas'] = time();` del PHP. */
export async function marcarVisitaActas() {
  await requireJunta('/');
  const cookieStore = await cookies();
  cookieStore.set('ultima_visita_actas', String(Date.now()), {
    httpOnly: true,
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 30,
    path: '/',
  });
}

export async function borrarActa(id: string) {
  await requireSuperadmin('/actas');
  const db = await getDb();
  await db.collection('actas').deleteOne({ _id: new ObjectId(id) });
  revalidatePath('/actas');
}
