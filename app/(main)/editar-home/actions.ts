'use server'

import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';
import { enviarPush, idsSociosAprobados } from '@/lib/push';
import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';

export async function guardarHome(formData: FormData) {
  await requireSuperadmin('/');

  const contenido = formData.get('contenido') as string;
  const textoImagen = formData.get('texto_imagen') as string;
  const imagen = formData.get('imagen') as File | null;
  const reunionTexto = formData.get('reunion_texto') as string;
  const reunionLugar = formData.get('reunion_lugar') as string;

  const db = await getDb();
  const actual = await db.collection('contenido_home').findOne({ seccion: 'bienvenida' });

  let imagenUrl = actual?.imagen ?? null;
  let hayImagenNueva = false;

  if (imagen && imagen.size > 0) {
    if (!extensionPermitidaImagen(imagen.name)) {
      redirect('/editar-home?error=formato');
    }
    const { url, error } = await uploadToCloudinary(imagen, 'ratas_home', 'image');
    if (error || !url) {
      redirect('/editar-home?error=cloudinary');
    }
    imagenUrl = url;
    hayImagenNueva = true;
  }

  await db.collection('contenido_home').updateOne(
    { seccion: 'bienvenida' },
    { $set: { contenido, texto_imagen: textoImagen, imagen: imagenUrl } },
    { upsert: true }
  );

  await Promise.all([
    db.collection('configuracion').updateOne(
      { clave: 'reunion_texto' },
      { $set: { valor: reunionTexto } },
      { upsert: true }
    ),
    db.collection('configuracion').updateOne(
      { clave: 'reunion_lugar' },
      { $set: { valor: reunionLugar } },
      { upsert: true }
    ),
  ]);

  if (hayImagenNueva) {
    const socios = await idsSociosAprobados();
    await enviarPush(socios, {
      title: '📸 Nueva novedad en el club',
      body: 'Han añadido una imagen nueva en la portada.',
      url: '/',
    });
  }

  revalidatePath('/');
  redirect('/editar-home?ok=1');
}
