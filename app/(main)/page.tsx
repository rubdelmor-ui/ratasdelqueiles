import Link from 'next/link';
import { getDb } from '@/lib/db';
import { getSession, esSuperadmin } from '@/lib/session';
import type { ContenidoHome } from '@/lib/types';

async function getContenidoHome(): Promise<ContenidoHome | null> {
  const db = await getDb();
  return db.collection<ContenidoHome>('contenido_home').findOne({ seccion: 'bienvenida' });
}

export default async function Home() {
  const [contenido, session] = await Promise.all([getContenidoHome(), getSession()]);
  const superadmin = esSuperadmin(session);

  const textoBienvenida =
    contenido?.contenido ||
    'La carretera espera. Únete a la próxima ruta o revisa las últimas novedades del club.';

  return (
    <>
      {/* HERO */}
      <section className="cut-panel relative overflow-hidden border border-steel/60 bg-surface px-6 pt-10 pb-8 text-center">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--color-rust)_0%,_transparent_60%)] opacity-[0.12]" />
        <div className="relative">
          <span className="skew-chip bg-hazard mx-auto mb-6">
            <span className="skew-chip-inner text-asphalt-low text-[11px] px-3 py-1">Club de moteros</span>
          </span>

          <div className="w-28 h-28 mx-auto mb-5 rounded-full border-[3px] border-rust p-1 shadow-[0_0_0_4px_rgba(0,0,0,0.5),0_0_40px_-8px_var(--color-rust)]">
            <img alt="Club Logo" className="w-full h-full object-cover rounded-full" src="/images/logo2.jpg" />
          </div>

          <h2 className="display-text text-4xl text-chrome leading-[0.95]">
            {session ? (
              <>
                Bienvenido<span className="text-rust">.</span>
                <br />
                {session.nombre}
              </>
            ) : (
              <>
                Bienvenidos<span className="text-rust">,</span>
                <br />
                Hermanos
              </>
            )}
          </h2>
          <p className="text-smoke mt-4 max-w-xs mx-auto whitespace-pre-line">{textoBienvenida}</p>
        </div>
      </section>

      {contenido?.texto_imagen && (
        <div className="cut-panel-sm bg-surface-high border border-steel/50 px-5 py-4 text-center">
          <p className="whitespace-pre-line text-chrome">{contenido.texto_imagen}</p>
        </div>
      )}

      <div>
        <span className="eyebrow flex items-center gap-2 mb-2">
          <span className="w-4 h-[2px] bg-rust" /> Últimas novedades
        </span>
        <div className="cut-panel bg-surface-high border border-steel/50 p-4 min-h-[140px] flex items-center justify-center">
          {contenido?.imagen ? (
            <img
              src={contenido.imagen}
              alt="Imagen de la home"
              className="max-w-full max-h-[300px] object-cover rounded"
            />
          ) : (
            <div className="text-ash flex flex-col items-center gap-2 py-6">
              <span className="material-symbols-outlined text-4xl">image</span>
              <span className="text-sm text-center">No hay imagen configurada.<br />El administrador puede subir una.</span>
            </div>
          )}
        </div>
      </div>

      {superadmin && (
        <Link
          href="/editar-home"
          title="Editar contenido de la home"
          className="fixed bottom-24 right-4 md:bottom-8 w-14 h-14 cut-panel-sm bg-rust text-asphalt-low flex items-center justify-center shadow-[0_8px_24px_-6px_var(--color-rust)] z-30 border-2 border-asphalt-low active:translate-y-0.5 transition-transform"
        >
          <span className="material-symbols-outlined">edit</span>
        </Link>
      )}
    </>
  );
}
