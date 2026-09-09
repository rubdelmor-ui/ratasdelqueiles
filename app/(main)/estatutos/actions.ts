'use server'

import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionEsPdf } from '@/lib/cloudinary';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function subirEstatutos(formData: FormData) {
  await requireSuperadmin('/estatutos');

  const archivo = formData.get('archivo_pdf') as File | null;
  if (!archivo || archivo.size === 0) {
    redirect('/estatutos');
  }
  if (!extensionEsPdf(archivo.name)) {
    redirect(`/estatutos?error=${encodeURIComponent('Error: solo se permiten archivos PDF.')}`);
  }

  const { url, error } = await uploadToCloudinary(archivo, 'ratas_estatutos', 'raw');
  if (error || !url) {
    redirect(`/estatutos?error=${encodeURIComponent('Error al subir a Cloudinary: ' + error)}`);
  }

  const db = await getDb();
  await db.collection('configuracion').updateOne(
    { clave: 'estatutos_pdf' },
    { $set: { valor: url } },
    { upsert: true }
  );

  revalidatePath('/estatutos');
  redirect('/estatutos?exito=1');
}

export async function borrarEstatutos() {
  await requireSuperadmin('/estatutos');

  const db = await getDb();
  await db.collection('configuracion').deleteOne({ clave: 'estatutos_pdf' });

  revalidatePath('/estatutos');
  redirect('/estatutos');
}
