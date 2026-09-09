'use server'

import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';
import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';

export async function actualizarSocio(id: string, formData: FormData) {
  await requireSuperadmin('/socios');

  const nombre = formData.get('nombre') as string;
  const email = formData.get('email') as string;
  const cargo = (formData.get('cargo') as string) || '';
  const aprobado = Number(formData.get('aprobado')) as 0 | 1;
  const rol = formData.get('rol') as 'socio' | 'junta';
  const foto = formData.get('foto') as File | null;

  const db = await getDb();
  const actual = await db.collection('usuarios').findOne({ _id: new ObjectId(id) });
  if (!actual) redirect('/socios');

  let fotoUrl: string | null = actual.foto ?? null;

  if (foto && foto.size > 0) {
    if (!extensionPermitidaImagen(foto.name)) {
      redirect(`/socios/${id}/editar?error=formato`);
    }
    const { url, error } = await uploadToCloudinary(foto, 'ratas_perfiles', 'image');
    if (!error && url) fotoUrl = url;
  }

  await db.collection('usuarios').updateOne(
    { _id: new ObjectId(id) },
    { $set: { nombre, email, rol, cargo, aprobado, foto: fotoUrl } }
  );

  revalidatePath('/socios');
  redirect('/socios');
}
