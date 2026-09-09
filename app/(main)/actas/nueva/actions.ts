'use server'

import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionEsPdf } from '@/lib/cloudinary';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function crearActa(formData: FormData) {
  await requireSuperadmin('/actas');

  const titulo = formData.get('titulo') as string;
  const fecha = formData.get('fecha_reunion') as string;
  const firmas = Number(formData.get('asistentes'));
  const autor = formData.get('autor') as string;
  const archivo = formData.get('archivo_pdf') as File | null;

  let archivoUrl: string | null = null;

  if (archivo && archivo.size > 0) {
    if (!extensionEsPdf(archivo.name)) {
      redirect('/actas/nueva?error=' + encodeURIComponent('Solo se permiten archivos PDF.'));
    }
    const { url, error } = await uploadToCloudinary(archivo, 'ratas_actas_pdf', 'raw');
    if (error || !url) {
      redirect(`/actas/nueva?error=${encodeURIComponent(`Cloudinary ha rechazado el PDF. Motivo: ${error}`)}`);
    }
    archivoUrl = url;
  }

  const db = await getDb();
  await db.collection('actas').insertOne({
    titulo,
    fecha_reunion: fecha,
    autor,
    firmas,
    archivo_pdf: archivoUrl,
    texto_acta: '',
    fecha_creacion: new Date(),
  });

  await db.collection('configuracion').updateOne(
    { clave: 'ultima_acta' },
    { $set: { valor: new Date().toISOString() } },
    { upsert: true }
  );

  revalidatePath('/actas');
  redirect('/actas');
}
