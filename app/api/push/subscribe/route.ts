import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/session';
import { guardarSuscripcion, eliminarSuscripcion } from '@/lib/push';

export async function POST(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'No autenticado' }, { status: 401 });

  const body = await req.json();
  if (!body?.endpoint || !body?.keys?.p256dh || !body?.keys?.auth) {
    return NextResponse.json({ error: 'Suscripción inválida' }, { status: 400 });
  }

  await guardarSuscripcion(session.id, {
    endpoint: body.endpoint,
    keys: { p256dh: body.keys.p256dh, auth: body.keys.auth },
  });

  return NextResponse.json({ ok: true });
}

export async function DELETE(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'No autenticado' }, { status: 401 });

  const { endpoint } = await req.json();
  if (endpoint) await eliminarSuscripcion(endpoint);
  return NextResponse.json({ ok: true });
}
