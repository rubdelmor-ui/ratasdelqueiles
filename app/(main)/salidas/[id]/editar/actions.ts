'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function actualizarSalida(id: string, formData: FormData) {
  await requireSuperadmin('/salidas');

  const destino = formData.get('destino') as string;
  const fecha = formData.get('fecha_salida') as string;
  const hora = formData.get('hora_quedada') as string;
  const punto = formData.get('punto_encuentro') as string;
  const descripcion = formData.get('descripcion') as string;
  const responsable = formData.get('responsable') as string;
  const imagenAntigua = formData.get('imagen_antigua') as string;
  const imagen = formData.get('imagen') as File | null;

  let imagenUrl: string | null = imagenAntigua || null;

  if (imagen && imagen.size > 0) {
    if (!extensionPermitidaImagen(imagen.name)) {
      redirect(`/salidas/${id}/editar?error=formato`);
    }
    const { url, error } = await uploadToCloudinary(imagen, 'ratas_salidas', 'image');
    if (error || !url) {
      redirect(`/salidas/${id}/editar?error=${encodeURIComponent(error || 'cloudinary')}`);
    }
    imagenUrl = url;
  }

  const db = await getDb();
  await db.collection('salidas').updateOne(
    { _id: new ObjectId(id) },
    {
      $set: {
        destino,
        fecha_salida: fecha,
        hora_quedada: hora,
        punto_encuentro: punto,
        descripcion,
        responsable,
        imagen: imagenUrl,
      },
    }
  );

  revalidatePath('/salidas');
  redirect('/salidas');
}
