import crypto from 'crypto';

type CloudinaryResourceType = 'image' | 'raw';

interface UploadResult {
  url: string | null;
  error: string | null;
}

/**
 * Sube un fichero a Cloudinary usando subida firmada (igual que el CURL + sha1
 * del PHP original), sin depender de un upload preset "unsigned".
 */
export async function uploadToCloudinary(
  file: File,
  folder: string,
  resourceType: CloudinaryResourceType = 'image'
): Promise<UploadResult> {
  const cloudName = process.env.CLOUDINARY_CLOUD_NAME;
  const apiKey = process.env.CLOUDINARY_API_KEY;
  const apiSecret = process.env.CLOUDINARY_API_SECRET;

  if (!cloudName || !apiKey || !apiSecret) {
    return { url: null, error: 'Cloudinary no está configurado en el servidor.' };
  }

  const timestamp = Math.floor(Date.now() / 1000);
  const signature = crypto
    .createHash('sha1')
    .update(`folder=${folder}&timestamp=${timestamp}${apiSecret}`)
    .digest('hex');

  const form = new FormData();
  form.append('file', file);
  form.append('api_key', apiKey);
  form.append('timestamp', String(timestamp));
  form.append('signature', signature);
  form.append('folder', folder);

  try {
    const res = await fetch(
      `https://api.cloudinary.com/v1_1/${cloudName}/${resourceType}/upload`,
      { method: 'POST', body: form }
    );
    const json = await res.json();
    if (json.secure_url) {
      return { url: json.secure_url as string, error: null };
    }
    return { url: null, error: json?.error?.message || 'Respuesta desconocida de Cloudinary.' };
  } catch {
    return { url: null, error: 'No se pudo contactar con Cloudinary.' };
  }
}

const EXTENSIONES_IMAGEN = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

export function extensionPermitidaImagen(nombreArchivo: string): boolean {
  const ext = nombreArchivo.split('.').pop()?.toLowerCase() || '';
  return EXTENSIONES_IMAGEN.includes(ext);
}

export function extensionEsPdf(nombreArchivo: string): boolean {
  return nombreArchivo.toLowerCase().endsWith('.pdf');
}
