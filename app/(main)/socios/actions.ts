'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireJunta, requireSuperadmin } from '@/lib/session';
import { revalidatePath } from 'next/cache';

export async function aprobarSocio(id: string) {
  await requireJunta('/');
  const db = await getDb();
  await db.collection('usuarios').updateOne({ _id: new ObjectId(id) }, { $set: { aprobado: 1 } });
  revalidatePath('/socios');
}

export async function rechazarSocio(id: string) {
  await requireJunta('/');
  const db = await getDb();
  await db.collection('usuarios').deleteOne({ _id: new ObjectId(id) });
  revalidatePath('/socios');
}

export async function hacerJunta(id: string) {
  const session = await requireSuperadmin('/socios');
  if (session.id === id) return;
  const db = await getDb();
  await db.collection('usuarios').updateOne({ _id: new ObjectId(id) }, { $set: { rol: 'junta' } });
  revalidatePath('/socios');
}

export async function quitarJunta(id: string) {
  const session = await requireSuperadmin('/socios');
  if (session.id === id) return;
  const db = await getDb();
  await db.collection('usuarios').updateOne({ _id: new ObjectId(id) }, { $set: { rol: 'socio' } });
  revalidatePath('/socios');
}

export async function eliminarSocio(id: string) {
  const session = await requireSuperadmin('/socios');
  if (session.id === id) return;
  const db = await getDb();
  await db.collection('usuarios').deleteOne({ _id: new ObjectId(id) });
  revalidatePath('/socios');
}
