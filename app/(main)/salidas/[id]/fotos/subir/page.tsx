import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ObjectId } from 'mongodb';
import { getDb } from '@/lib/db';
import { requireSession } from '@/lib/session';
import PageHeader from '@/components/PageHeader';
import SubirFotos from '@/components/salidas/SubirFotos';

export default async function SubirFotosSalidaPage({ params }: { params: Promise<{ id: string }> }) {
  await requireSession('/login');
  const { id } = await params;

  if (!ObjectId.isValid(id)) notFound();

  const db = await getDb();
  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(id) }, { projection: { destino: 1 } });
  if (!salida) notFound();

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        eyebrow="Cuelga tus fotos"
        title={salida.destino}
        subtitle="Sube las fotos que hayas hecho en esta salida. Podrás elegir varias a la vez."
        action={
          <Link href="/salidas" className="text-smoke hover:text-rust">
            <span className="material-symbols-outlined">close</span>
          </Link>
        }
      />

      <SubirFotos salidaId={id} />

      <Link href={`/salidas/${id}/fotos`} className="btn btn-ghost btn-block">
        <span className="material-symbols-outlined text-[20px]">photo_library</span>
        Ver fotos de esta salida
      </Link>
    </div>
  );
}
