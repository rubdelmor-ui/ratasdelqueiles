'use server'

import { getDb } from '@/lib/db';
import bcrypt from 'bcryptjs';
import { uploadToCloudinary, extensionPermitidaImagen } from '@/lib/cloudinary';

export async function registrarUsuario(formData: FormData) {
  const nombre = formData.get('nombre') as string;
  const email = formData.get('email') as string;
  const password = formData.get('password') as string;
  const passwordConfirm = formData.get('password_confirm') as string;
  const pregunta = formData.get('pregunta') as string;
  const respuesta = formData.get('respuesta') as string;
  const foto = formData.get('foto') as File | null;

  if (password !== passwordConfirm) {
    return { error: '❌ Las contraseñas no coinciden.' };
  }

  const db = await getDb();

  const usuarioExistente = await db.collection('usuarios').findOne({ email });
  if (usuarioExistente) {
    return { error: '❌ Este correo electrónico ya está registrado.' };
  }

  const passwordHash = await bcrypt.hash(password, 10);
  const respuestaHash = await bcrypt.hash(respuesta.trim().toLowerCase(), 10);

  let fotoUrl: string | null = null;

  if (foto && foto.size > 0) {
    if (!extensionPermitidaImagen(foto.name)) {
      return { error: '❌ Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.' };
    }
    const { url, error } = await uploadToCloudinary(foto, 'ratas_perfiles', 'image');
    if (error) {
      return { error: `❌ Error al subir la foto: ${error}` };
    }
    fotoUrl = url;
  }

  await db.collection('usuarios').insertOne({
    nombre,
    email,
    password: passwordHash,
    rol: 'socio',
    aprobado: 0,
    foto: fotoUrl,
    pregunta_seguridad: pregunta,
    respuesta_seguridad: respuestaHash,
    fecha_registro: new Date(),
  });

  return { success: true };
}
