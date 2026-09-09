'use server'

import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';
import { enviarPush, idsSociosAprobados } from '@/lib/push';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function crearSalida(formData: FormData) {
  await requireSuperadmin('/salidas');

  const destino = formData.get('destino') as string;
  const fecha = formData.get('fecha_salida') as string;
  const hora = formData.get('hora_quedada') as string;
  const punto = formData.get('punto_encuentro') as string;
  const descripcion = formData.get('descripcion') as string;
  const responsable = formData.get('responsable') as string;
  const imagen = formData.get('imagen') as File | null;

  let imagenUrl: string | null = null;

  if (imagen && imagen.size > 0) {
    if (!extensionPermitidaImagen(imagen.name)) {
      redirect('/salidas/nueva?error=formato');
    }
    const { url, error } = await uploadToCloudinary(imagen, 'ratas_salidas', 'image');
    if (error || !url) {
      redirect(`/salidas/nueva?error=${encodeURIComponent(error || 'cloudinary')}`);
    }
    imagenUrl = url;
  }

  const db = await getDb();
  await db.collection('salidas').insertOne({
    destino,
    fecha_salida: fecha,
    hora_quedada: hora,
    punto_encuentro: punto,
    descripcion,
    imagen: imagenUrl,
    responsable,
    fecha_creacion: new Date(),
  });

  const socios = await idsSociosAprobados();
  await enviarPush(socios, {
    title: '🏍️ Nueva salida programada',
    body: `${destino} · ${new Date(`${fecha}T${hora}`).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })}`,
    url: '/salidas',
  });

  revalidatePath('/salidas');
  redirect('/salidas');
}
