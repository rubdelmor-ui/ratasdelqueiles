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

export async function subirFoto(formData: FormData): Promise<{ error?: string }> {
  const session = await requireSession('/login');

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

  const db = await getDb();
  await db.collection('fotos_salidas').insertOne({
    url,
    usuario_id: new ObjectId(session.id),
    usuario_nombre: session.nombre,
    fecha_subida: new Date(),
  });

  revalidatePath('/salidas/fotos');
  return {};
}

export async function borrarFoto(id: string) {
  await requireSuperadmin('/salidas/fotos');

  const db = await getDb();
  const foto = await db.collection('fotos_salidas').findOne({ _id: new ObjectId(id) });
  if (!foto) return;

  await db.collection('fotos_salidas').deleteOne({ _id: foto._id });
  await borrarImagenDeCloudinary(foto.url as string);

  revalidatePath('/salidas/fotos');
}
