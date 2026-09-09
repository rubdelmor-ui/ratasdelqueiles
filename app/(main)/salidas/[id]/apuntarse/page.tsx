import { redirect } from 'next/navigation';
import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSession } from '@/lib/session';
import ApuntarseForm from '@/components/salidas/ApuntarseForm';
import { apuntarseConAcompanantes } from './actions';

export default async function ApuntarseSalidaPage({ params }: { params: Promise<{ id: string }> }) {
  const session = await requireSession('/login');
  const { id } = await params;

  const db = await getDb();
  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(id) });
  if (!salida) redirect('/salidas');

  const yaInscrito = await db.collection('inscripciones').findOne({
    salida_id: new ObjectId(id),
    usuario_id: new ObjectId(session.id),
  });
  if (yaInscrito) redirect('/salidas');

  const accion = apuntarseConAcompanantes.bind(null, id);

  return (
    <div className="flex-grow flex items-center justify-center py-6">
      <div className="cut-panel bg-surface border border-steel/50 p-6 max-w-sm w-full">
        <span className="eyebrow">Apuntarse a</span>
        <h2 className="display-text text-2xl text-rust mt-0.5 mb-5">{salida.destino}</h2>
        <ApuntarseForm action={accion} />
      </div>
    </div>
  );
}
