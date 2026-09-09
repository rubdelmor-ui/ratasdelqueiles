import webpush from 'web-push';
import { ObjectId } from 'mongodb';
import { getDb } from './db';

let vapidConfigured = false;

function configurarVapid() {
  if (vapidConfigured) return;
  const publicKey = process.env.NEXT_PUBLIC_VAPID_PUBLIC_KEY;
  const privateKey = process.env.VAPID_PRIVATE_KEY;
  const subject = process.env.VAPID_SUBJECT;
  if (!publicKey || !privateKey || !subject) {
    throw new Error('Faltan las claves VAPID en las variables de entorno.');
  }
  webpush.setVapidDetails(subject, publicKey, privateKey);
  vapidConfigured = true;
}

export interface PushPayload {
  title: string;
  body: string;
  url?: string;
}

interface PushSubscriptionDoc {
  _id: ObjectId;
  usuario_id: ObjectId;
  endpoint: string;
  keys: { p256dh: string; auth: string };
}

export async function guardarSuscripcion(
  usuarioId: string,
  subscription: { endpoint: string; keys: { p256dh: string; auth: string } }
) {
  const db = await getDb();
  await db.collection('push_subscriptions').updateOne(
    { endpoint: subscription.endpoint },
    {
      $set: {
        usuario_id: new ObjectId(usuarioId),
        endpoint: subscription.endpoint,
        keys: subscription.keys,
        fecha_actualizacion: new Date(),
      },
    },
    { upsert: true }
  );
}

export async function eliminarSuscripcion(endpoint: string) {
  const db = await getDb();
  await db.collection('push_subscriptions').deleteOne({ endpoint });
}

/** Envía una notificación push a un conjunto de usuarios. Si una suscripción
 * ya no es válida (dispositivo desinstaló la app, permiso revocado...), la
 * borra silenciosamente en vez de reintentar. */
export async function enviarPush(usuarioIds: string[], payload: PushPayload) {
  if (usuarioIds.length === 0) return;
  configurarVapid();

  const db = await getDb();
  const objectIds = usuarioIds.map((id) => new ObjectId(id));
  const suscripciones = await db
    .collection<PushSubscriptionDoc>('push_subscriptions')
    .find({ usuario_id: { $in: objectIds } })
    .toArray();

  const body = JSON.stringify(payload);

  await Promise.all(
    suscripciones.map(async (sub) => {
      try {
        await webpush.sendNotification(
          { endpoint: sub.endpoint, keys: sub.keys },
          body
        );
      } catch (err) {
        const status = (err as { statusCode?: number }).statusCode;
        if (status === 404 || status === 410) {
          await db.collection('push_subscriptions').deleteOne({ _id: sub._id });
        } else {
          console.error('Error enviando push:', err);
        }
      }
    })
  );
}

/** IDs de todos los socios aprobados (para avisos generales del club). */
export async function idsSociosAprobados(): Promise<string[]> {
  const db = await getDb();
  const usuarios = await db
    .collection('usuarios')
    .find({ aprobado: 1 }, { projection: { _id: 1 } })
    .toArray();
  return usuarios.map((u) => u._id.toString());
}

/** IDs de todos los miembros de la junta. */
export async function idsJunta(): Promise<string[]> {
  const db = await getDb();
  const usuarios = await db
    .collection('usuarios')
    .find({ rol: 'junta' }, { projection: { _id: 1 } })
    .toArray();
  return usuarios.map((u) => u._id.toString());
}

/** ID del superadmin (por email), si existe como usuario. */
export async function idSuperadmin(): Promise<string[]> {
  const db = await getDb();
  const superadminEmail = process.env.SUPERADMIN_EMAIL || 'admin@club.com';
  const usuario = await db.collection('usuarios').findOne({ email: superadminEmail }, { projection: { _id: 1 } });
  return usuario ? [usuario._id.toString()] : [];
}
