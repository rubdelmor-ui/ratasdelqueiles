'use client'

import { useState } from 'react';
import { registrarUsuario } from './actions';
import Link from 'next/link';

export default function RegistroPage() {
  const [mostrarPassword, setMostrarPassword] = useState(false);
  const [mensaje, setMensaje] = useState<{ error?: string; success?: boolean } | null>(null);
  const [cargando, setCargando] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setCargando(true);
    setMensaje(null);

    const formData = new FormData(e.currentTarget);
    const resultado = await registrarUsuario(formData);

    if (resultado.error) {
      setMensaje({ error: resultado.error });
    } else if (resultado.success) {
      setMensaje({ success: true });
      (e.target as HTMLFormElement).reset();
    }
    setCargando(false);
  }

  return (
    <div className="flex-grow flex flex-col items-center justify-center p-5">
      <div className="w-full max-w-md">
        <div className="flex flex-col items-center text-center mb-8">
          <div className="w-24 h-24 rounded-full border-[3px] border-rust p-1 shadow-[0_0_0_4px_rgba(0,0,0,0.5),0_0_30px_-6px_var(--color-rust)] mb-5">
            <img alt="Logo" className="w-full h-full object-cover rounded-full" src="/images/logo2.jpg" />
          </div>
          <h1 className="display-text text-3xl text-chrome leading-tight">
            Ratas <span className="text-rust">del Queiles</span>
          </h1>
          <p className="eyebrow mt-2">Registro de nuevo socio</p>
        </div>

        <div className="cut-panel bg-surface border border-steel/60 p-7">
          {mensaje?.error && (
            <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded mb-5 text-sm">
              {mensaje.error}
            </div>
          )}

          {mensaje?.success && (
            <div className="bg-moss/15 border border-moss/40 text-chrome p-3 rounded mb-5 text-sm">
              ✅ ¡Registro exitoso! Tu solicitud está pendiente de aprobación.{' '}
              <Link href="/login" className="font-bold underline text-moss">
                Ir a iniciar sesión
              </Link>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="field-label">Nombre y apellidos *</label>
              <input type="text" name="nombre" required className="field-input" />
            </div>

            <div>
              <label className="field-label">Correo electrónico *</label>
              <input type="email" name="email" required className="field-input" />
            </div>

            <div className="relative">
              <label className="field-label">Contraseña *</label>
              <input
                type={mostrarPassword ? 'text' : 'password'}
                name="password"
                required
                className="field-input pr-16"
              />
              <button
                type="button"
                onClick={() => setMostrarPassword(!mostrarPassword)}
                className="absolute right-2 top-[34px] text-ash hover:text-rust text-xs font-mono uppercase px-2 py-1"
              >
                {mostrarPassword ? 'Ocultar' : 'Mostrar'}
              </button>
            </div>

            <div>
              <label className="field-label">Repetir contraseña *</label>
              <input type={mostrarPassword ? 'text' : 'password'} name="password_confirm" required className="field-input" />
            </div>

            <div>
              <label className="field-label">Pregunta de seguridad *</label>
              <select name="pregunta" required className="field-input appearance-none">
                <option value="">Selecciona una pregunta…</option>
                <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                <option value="¿Cuál es tu ciudad natal?">¿Cuál es tu ciudad natal?</option>
                <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
              </select>
            </div>

            <div>
              <label className="field-label">Respuesta *</label>
              <input type="text" name="respuesta" required className="field-input" />
            </div>

            <div>
              <label className="field-label">Foto de perfil (opcional)</label>
              <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp" className="field-input py-2" />
            </div>

            <button type="submit" disabled={cargando} className="btn btn-primary btn-block mt-2">
              <span className="material-symbols-outlined">send</span>
              {cargando ? 'Enviando…' : 'Enviar solicitud'}
            </button>
          </form>

          <div className="mt-6 pt-5 border-t border-dashed border-steel/50 text-center">
            <Link href="/login" className="text-smoke hover:text-rust text-xs font-mono uppercase tracking-wide transition-colors">
              ← ¿Ya tienes cuenta? Inicia sesión
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
