'use server'

import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';
import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';

export async function guardarHome(formData: FormData) {
  await requireSuperadmin('/');

  const contenido = formData.get('contenido') as string;
  const textoImagen = formData.get('texto_imagen') as string;
  const imagen = formData.get('imagen') as File | null;

  const db = await getDb();
  const actual = await db.collection('contenido_home').findOne({ seccion: 'bienvenida' });

  let imagenUrl = actual?.imagen ?? null;

  if (imagen && imagen.size > 0) {
    if (!extensionPermitidaImagen(imagen.name)) {
      redirect('/editar-home?error=formato');
    }
    const { url, error } = await uploadToCloudinary(imagen, 'ratas_home', 'image');
    if (error || !url) {
      redirect('/editar-home?error=cloudinary');
    }
    imagenUrl = url;
  }

  await db.collection('contenido_home').updateOne(
    { seccion: 'bienvenida' },
    { $set: { contenido, texto_imagen: textoImagen, imagen: imagenUrl } },
    { upsert: true }
  );

  revalidatePath('/');
  redirect('/editar-home?ok=1');
}
