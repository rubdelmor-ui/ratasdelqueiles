'use client'

import { useEffect, useState } from 'react';

function urlBase64ToUint8Array(base64String: string) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
}

type Estado = 'no-soportado' | 'bloqueado' | 'activando' | 'activo' | 'inactivo';

export default function NotificationToggle() {
  const [estado, setEstado] = useState<Estado>('inactivo');

  useEffect(() => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      setEstado('no-soportado');
      return;
    }
    if (Notification.permission === 'denied') {
      setEstado('bloqueado');
      return;
    }
    navigator.serviceWorker.ready.then(async (reg) => {
      const sub = await reg.pushManager.getSubscription();
      setEstado(sub ? 'activo' : 'inactivo');
    });
  }, []);

  async function activar() {
    setEstado('activando');
    try {
      const permiso = await Notification.requestPermission();
      if (permiso !== 'granted') {
        setEstado(permiso === 'denied' ? 'bloqueado' : 'inactivo');
        return;
      }

      const reg = await navigator.serviceWorker.ready;
      const publicKey = process.env.NEXT_PUBLIC_VAPID_PUBLIC_KEY!;
      const sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
      });

      await fetch('/api/push/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(sub),
      });

      setEstado('activo');
    } catch (err) {
      console.error('No se pudo activar notificaciones:', err);
      setEstado('inactivo');
    }
  }

  async function desactivar() {
    try {
      const reg = await navigator.serviceWorker.ready;
      const sub = await reg.pushManager.getSubscription();
      if (sub) {
        await fetch('/api/push/subscribe', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ endpoint: sub.endpoint }),
        });
        await sub.unsubscribe();
      }
      setEstado('inactivo');
    } catch (err) {
      console.error('No se pudo desactivar notificaciones:', err);
    }
  }

  if (estado === 'no-soportado') return null;

  if (estado === 'bloqueado') {
    return (
      <div className="px-4 py-2.5 text-ash text-xs">
        🔕 Notificaciones bloqueadas por el navegador. Actívalas desde los ajustes del sitio.
      </div>
    );
  }

  return (
    <button
      onClick={estado === 'activo' ? desactivar : activar}
      disabled={estado === 'activando'}
      className="w-full text-left flex items-center gap-2 px-4 py-2.5 text-smoke hover:bg-surface-high hover:text-rust transition-colors text-sm disabled:opacity-50"
    >
      <span className="material-symbols-outlined text-[18px]">
        {estado === 'activo' ? 'notifications_active' : 'notifications'}
      </span>
      {estado === 'activo' && 'Notificaciones activadas'}
      {estado === 'inactivo' && 'Activar notificaciones'}
      {estado === 'activando' && 'Activando…'}
    </button>
  );
}
