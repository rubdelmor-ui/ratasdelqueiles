import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import { inputDarkClass, labelDarkClass } from '@/lib/ui';
import type { Usuario } from '@/lib/types';
import PageHeader from '@/components/PageHeader';
import { actualizarSocio } from './actions';

export default async function EditarSocioPage({ params }: { params: Promise<{ id: string }> }) {
  await requireSuperadmin('/socios');
  const { id } = await params;

  const db = await getDb();
  const socio = await db.collection<Usuario>('usuarios').findOne({ _id: new ObjectId(id) });
  if (!socio) notFound();

  const actualizarConId = actualizarSocio.bind(null, id);

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Panel de junta"
        title="Editar Socio"
        action={
          <Link href="/socios" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <div className="cut-panel bg-surface border border-steel/50 p-5">
        <form action={actualizarConId} className="space-y-4">
          <div>
            <label className={labelDarkClass}>Nombre</label>
            <input type="text" name="nombre" defaultValue={socio.nombre} required className={inputDarkClass} />
          </div>
          <div>
            <label className={labelDarkClass}>Email</label>
            <input type="email" name="email" defaultValue={socio.email} required className={inputDarkClass} />
          </div>

          <div>
            <label className={labelDarkClass}>Rol</label>
            <select name="rol" defaultValue={socio.rol} className={inputDarkClass}>
              <option value="socio">Socio</option>
              <option value="junta">Junta</option>
            </select>
          </div>

          <div>
            <label className={labelDarkClass}>Cargo (solo junta)</label>
            <input
              type="text"
              name="cargo"
              defaultValue={socio.cargo || ''}
              placeholder="Ej: Presidente, Secretario, Tesorero..."
              className={inputDarkClass}
            />
          </div>

          <div>
            <label className={labelDarkClass}>Estado</label>
            <select name="aprobado" defaultValue={String(socio.aprobado)} className={inputDarkClass}>
              <option value="1">Aprobado</option>
              <option value="0">Pendiente</option>
            </select>
          </div>

          <div>
            <label className={labelDarkClass}>Foto actual</label>
            {socio.foto ? (
              <img src={socio.foto} className="w-20 h-20 rounded-full object-cover border-2 border-steel/50" alt="Foto" />
            ) : (
              <p className="text-smoke text-sm">Sin foto o no es de la nube</p>
            )}
          </div>

          <div>
            <label className={labelDarkClass}>Cambiar foto (opcional)</label>
            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp" className={`${inputDarkClass} py-2`} />
          </div>

          <div className="flex gap-3 pt-4 border-t border-dashed border-steel/50">
            <button type="submit" className="btn btn-primary flex-1">
              <span className="material-symbols-outlined text-[18px]">save</span>
              Guardar
            </button>
            <Link href="/socios" className="btn btn-ghost flex-1">
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
