'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSession, requireSuperadmin } from '@/lib/session';
import { revalidatePath } from 'next/cache';

/** Alterna la inscripción del socio actual a una salida (apuntarse.php). */
export async function toggleApuntarse(salidaId: string) {
  const session = await requireSession('/login');
  const db = await getDb();

  const filtro = { salida_id: new ObjectId(salidaId), usuario_id: new ObjectId(session.id) };
  const existente = await db.collection('inscripciones').findOne(filtro);

  if (existente) {
    await db.collection('acompanantes').deleteMany({ inscripcion_id: existente._id });
    await db.collection('inscripciones').deleteOne({ _id: existente._id });
  } else {
    await db.collection('inscripciones').insertOne({ ...filtro, fecha_inscripcion: new Date() });
  }

  revalidatePath('/salidas');
}

export async function borrarSalida(salidaId: string) {
  await requireSuperadmin('/salidas');
  const db = await getDb();

  const inscripciones = await db
    .collection('inscripciones')
    .find({ salida_id: new ObjectId(salidaId) })
    .toArray();
  const inscripcionIds = inscripciones.map((i) => i._id);

  await db.collection('acompanantes').deleteMany({ inscripcion_id: { $in: inscripcionIds } });
  await db.collection('inscripciones').deleteMany({ salida_id: new ObjectId(salidaId) });
  await db.collection('salidas').deleteOne({ _id: new ObjectId(salidaId) });

  revalidatePath('/salidas');
}
