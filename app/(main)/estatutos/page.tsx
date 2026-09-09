import { getDb } from '@/lib/db';
import { getSession, esSuperadmin } from '@/lib/session';
import PageHeader from '@/components/PageHeader';
import ConfirmSubmitButton from '@/components/ConfirmSubmitButton';
import { subirEstatutos, borrarEstatutos } from './actions';

export default async function EstatutosPage({
  searchParams,
}: {
  searchParams: Promise<{ exito?: string; error?: string }>;
}) {
  const { exito, error } = await searchParams;
  const session = await getSession();
  const superadmin = esSuperadmin(session);

  const db = await getDb();
  const config = await db.collection('configuracion').findOne({ clave: 'estatutos_pdf' });
  const pdfEstatutos = config?.valor as string | undefined;

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Documentación oficial"
        title="Estatutos del Club"
        subtitle="Normas fundamentales y código de conducta."
        action={
          superadmin && (
            <span className="skew-chip bg-hazard flex-shrink-0">
              <span className="skew-chip-inner text-asphalt-low text-[10px] px-2.5 py-1">👑 Superadmin</span>
            </span>
          )
        }
      />

      {exito && <div className="bg-moss/15 border border-moss/40 text-chrome p-3 rounded text-sm">✅ Estatutos actualizados correctamente.</div>}
      {error && <div className="bg-ember/15 border border-ember/40 text-flame p-3 rounded text-sm">{decodeURIComponent(error)}</div>}

      <div className="cut-panel bg-surface border border-steel/50 p-8 text-center">
        {pdfEstatutos ? (
          <>
            <span className="material-symbols-outlined text-6xl text-rust">picture_as_pdf</span>
            <h3 className="display-text text-lg text-chrome mt-4">Estatutos disponibles</h3>
            <p className="text-smoke text-sm mt-1">Guardados en la nube segura</p>
            <a href={pdfEstatutos} target="_blank" rel="noreferrer" className="btn btn-danger mt-5">
              <span className="material-symbols-outlined text-[18px]">description</span>
              Ver Estatutos
            </a>
            {superadmin && (
              <form action={borrarEstatutos} className="mt-2">
                <ConfirmSubmitButton
                  confirmMessage="¿Eliminar los estatutos actuales? Los socios dejarán de ver el PDF hasta que subas uno nuevo."
                  className="btn btn-ghost btn-sm"
                >
                  <span className="material-symbols-outlined text-[16px] text-ember">delete</span>
                  Eliminar
                </ConfirmSubmitButton>
              </form>
            )}
          </>
        ) : (
          <>
            <span className="material-symbols-outlined text-6xl text-ash">description</span>
            <h3 className="display-text text-lg text-chrome mt-4">No hay estatutos disponibles</h3>
            <p className="text-smoke text-sm mt-1">El documento aún no ha sido subido por la administración.</p>
          </>
        )}
      </div>

      {superadmin && (
        <div className="cut-panel-sm bg-surface border border-steel/50 p-5">
          <h3 className="eyebrow text-rust mb-3">🛠️ Gestión de estatutos</h3>
          <form action={subirEstatutos} className="flex flex-col gap-3">
            <div>
              <label className="field-label">Selecciona un nuevo archivo PDF:</label>
              <input type="file" name="archivo_pdf" accept=".pdf" required className="field-input py-2" />
            </div>
            <button type="submit" className="btn btn-success self-start">
              <span className="material-symbols-outlined text-[18px]">cloud_upload</span>
              Subir / Actualizar
            </button>
          </form>
        </div>
      )}
    </div>
  );
}
