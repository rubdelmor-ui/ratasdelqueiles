'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSession, requireSuperadmin } from '@/lib/session';
import {
  uploadToCloudinary,
  borrarImagenDeCloudinary,
  extensionPermitidaImagen,
} from '@/lib/cloudinary';
import { revalidatePath } from 'next/cache';

export async function subirFoto(salidaId: string, formData: FormData): Promise<{ error?: string }> {
  const session = await requireSession('/login');

  if (!ObjectId.isValid(salidaId)) {
    return { error: 'Salida no válida.' };
  }
  const db = await getDb();
  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(salidaId) }, { projection: { _id: 1 } });
  if (!salida) {
    return { error: 'La salida ya no existe.' };
  }

  const foto = formData.get('foto') as File | null;
  if (!foto || foto.size === 0) {
    return { error: 'No se ha recibido ninguna foto.' };
  }
  if (!extensionPermitidaImagen(foto.name)) {
    return { error: 'Formato no permitido. Usa JPG, PNG, GIF o WEBP.' };
  }

  const { url, error } = await uploadToCloudinary(foto, 'ratas_fotos_salidas', 'image');
  if (error || !url) {
    return { error: error || 'Error al subir la foto.' };
  }

  await db.collection('fotos_salidas').insertOne({
    salida_id: salida._id,
    url,
    usuario_id: new ObjectId(session.id),
    usuario_nombre: session.nombre,
    fecha_subida: new Date(),
  });

  revalidatePath(`/salidas/${salidaId}/fotos`);
  revalidatePath('/salidas');
  return {};
}

export async function borrarFoto(id: string) {
  await requireSuperadmin('/salidas');

  const db = await getDb();
  const foto = await db.collection('fotos_salidas').findOne({ _id: new ObjectId(id) });
  if (!foto) return;

  await db.collection('fotos_salidas').deleteOne({ _id: foto._id });
  await borrarImagenDeCloudinary(foto.url as string);

  revalidatePath(`/salidas/${foto.salida_id}/fotos`);
  revalidatePath('/salidas');
}
