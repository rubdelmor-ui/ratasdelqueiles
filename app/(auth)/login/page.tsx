'use client'

import { useState } from 'react';
import { loginUsuario } from './actions';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const [mostrarPassword, setMostrarPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [cargando, setCargando] = useState(false);
  const router = useRouter();

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setCargando(true);
    setError(null);

    const formData = new FormData(e.currentTarget);
    const resultado = await loginUsuario(formData);

    if (resultado?.error) {
      setError(resultado.error);
      setCargando(false);
    } else if (resultado?.success) {
      router.push('/');
      router.refresh();
    }
  }

  return (
    <div className="flex-grow flex flex-col items-center justify-center p-5">
      <div className="w-full max-w-sm">
        <div className="flex flex-col items-center text-center mb-8">
          <div className="w-24 h-24 rounded-full border-[3px] border-rust p-1 shadow-[0_0_0_4px_rgba(0,0,0,0.5),0_0_30px_-6px_var(--color-rust)] mb-5">
            <img alt="Logo" className="w-full h-full object-cover rounded-full" src="/images/logo2.jpg" />
          </div>
          <h1 className="display-text text-3xl text-chrome leading-tight">
            Ratas <span className="text-rust">del Queiles</span>
          </h1>
          <p className="eyebrow mt-2">Accede a tu cuenta de socio</p>
        </div>

        <div className="cut-panel bg-surface border border-steel/60 p-7">
          {error && (
            <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded mb-5 text-sm">{error}</div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="field-label">Correo electrónico</label>
              <input type="email" name="email" required placeholder="tuemail@ejemplo.com" className="field-input" />
            </div>

            <div className="relative">
              <label className="field-label">Contraseña</label>
              <input
                type={mostrarPassword ? 'text' : 'password'}
                name="password"
                required
                placeholder="••••••••"
                className="field-input pr-16"
              />
              <button
                type="button"
                onClick={() => setMostrarPassword(!mostrarPassword)}
                className="absolute right-2 top-[34px] text-ash hover:text-rust text-xs font-mono uppercase px-2 py-1 flex items-center gap-1"
              >
                <span className="material-symbols-outlined text-[18px]">
                  {mostrarPassword ? 'visibility_off' : 'visibility'}
                </span>
              </button>
            </div>

            <button type="submit" disabled={cargando} className="btn btn-primary btn-block mt-2">
              <span className="material-symbols-outlined">sports_motorsports</span>
              {cargando ? 'Entrando…' : 'Entrar al club'}
            </button>
          </form>

          <div className="mt-6 pt-5 border-t border-dashed border-steel/50 text-center space-y-3">
            <Link href="/olvide_password" className="block text-smoke hover:text-rust text-xs font-mono uppercase tracking-wide transition-colors">
              🔑 ¿Olvidaste tu contraseña?
            </Link>
            <Link href="/registro" className="block text-smoke hover:text-rust text-xs font-mono uppercase tracking-wide transition-colors">
              ¿No tienes cuenta? Regístrate aquí
            </Link>
            <Link href="/" className="block text-ash hover:text-rust text-xs font-mono uppercase tracking-wide transition-colors">
              ← Volver al inicio
            </Link>
          </div>
        </div>

        <p className="text-center mt-6 text-ash text-[11px] font-mono uppercase tracking-widest">
          🐀 Ratas del Queiles · Todos los derechos reservados
        </p>
      </div>
    </div>
  );
}
