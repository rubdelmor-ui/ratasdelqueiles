'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSession } from '@/lib/session';
import { enviarPush, idSuperadmin } from '@/lib/push';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function apuntarseConAcompanantes(salidaId: string, formData: FormData) {
  const session = await requireSession('/login');
  const db = await getDb();

  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(salidaId) });
  if (!salida) redirect('/salidas');

  const yaInscrito = await db.collection('inscripciones').findOne({
    salida_id: new ObjectId(salidaId),
    usuario_id: new ObjectId(session.id),
  });

  if (!yaInscrito) {
    const { insertedId } = await db.collection('inscripciones').insertOne({
      salida_id: new ObjectId(salidaId),
      usuario_id: new ObjectId(session.id),
      fecha_inscripcion: new Date(),
    });

    let numAcompanantes = 0;
    const conAcompanantes = formData.get('acompanantes') === '1';
    if (conAcompanantes) {
      const nombres = formData.getAll('acompanante').map((n) => (n as string).trim()).filter(Boolean);
      if (nombres.length > 0) {
        await db.collection('acompanantes').insertMany(
          nombres.map((nombre) => ({ inscripcion_id: insertedId, nombre }))
        );
        numAcompanantes = nombres.length;
      }
    }

    try {
      await enviarPush(await idSuperadmin(), {
        title: 'NUEVA INSCRIPCIÓN',
        body: `${session.nombre} se ha apuntado a ${salida.destino}\n${
          numAcompanantes === 0
            ? 'Sin acompañantes'
            : `Con ${numAcompanantes} acompañante${numAcompanantes > 1 ? 's' : ''}`
        }`,
        url: '/salidas',
      });
    } catch (err) {
      console.error('Error enviando push de inscripción:', err);
    }
  }

  revalidatePath('/salidas');
  redirect('/salidas');
}
