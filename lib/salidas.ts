import { ObjectId } from 'mongodb';
import { getDb } from './db';

export interface AsistenteConAcompanantes {
  socio: string;
  acompanantes: string[];
}

export interface SalidaConDatos {
  id: string;
  destino: string;
  fechaSalida: string;
  horaQuedada: string;
  fechaIso: string;
  puntoEncuentro: string;
  descripcion: string;
  imagen: string | null;
  responsable: string;
  totalInscritos: number;
  listaAsistentes: AsistenteConAcompanantes[];
  yaApuntado: boolean;
}

/** Replica las consultas de salidas.php: próximas salidas + conteo y listado de
 * asistentes (socios + acompañantes) para cada una. */
export async function getSalidasConDatos(usuarioId?: string): Promise<SalidaConDatos[]> {
  const db = await getDb();
  const hoy = new Date().toISOString().slice(0, 10);

  const salidas = await db
    .collection('salidas')
    .find({ fecha_salida: { $gte: hoy } })
    .sort({ fecha_salida: 1 })
    .toArray();

  const resultado: SalidaConDatos[] = [];

  for (const salida of salidas) {
    const inscripciones = await db
      .collection('inscripciones')
      .aggregate([
        { $match: { salida_id: salida._id } },
        {
          $lookup: {
            from: 'usuarios',
            localField: 'usuario_id',
            foreignField: '_id',
            as: 'usuario',
          },
        },
        { $unwind: '$usuario' },
        {
          $lookup: {
            from: 'acompanantes',
            localField: '_id',
            foreignField: 'inscripcion_id',
            as: 'acompanantes',
          },
        },
        { $sort: { 'usuario.nombre': 1 } },
      ])
      .toArray();

    const listaAsistentes: AsistenteConAcompanantes[] = inscripciones.map((i) => ({
      socio: i.usuario.nombre,
      acompanantes: (i.acompanantes as { nombre: string }[])
        .map((a) => a.nombre)
        .sort((a, b) => a.localeCompare(b)),
    }));

    const totalInscritos = listaAsistentes.reduce((acc, a) => acc + 1 + a.acompanantes.length, 0);

    const yaApuntado = usuarioId
      ? inscripciones.some((i) => i.usuario_id.toString() === usuarioId)
      : false;

    let fechaIso = '';
    if (salida.fecha_salida && salida.hora_quedada) {
      const dt = new Date(`${salida.fecha_salida}T${salida.hora_quedada}`);
      if (!isNaN(dt.getTime())) fechaIso = dt.toISOString();
    }

    resultado.push({
      id: salida._id.toString(),
      destino: salida.destino,
      fechaSalida: salida.fecha_salida,
      horaQuedada: salida.hora_quedada,
      fechaIso,
      puntoEncuentro: salida.punto_encuentro,
      descripcion: salida.descripcion,
      imagen: salida.imagen || null,
      responsable: salida.responsable,
      totalInscritos,
      listaAsistentes,
      yaApuntado,
    });
  }

  return resultado;
}

export async function getAsistentesParaExcel(salidaId: string) {
  const db = await getDb();
  const salida = await db.collection('salidas').findOne({ _id: new ObjectId(salidaId) });
  if (!salida) return null;

  const inscripciones = await db
    .collection('inscripciones')
    .aggregate([
      { $match: { salida_id: new ObjectId(salidaId) } },
      {
        $lookup: { from: 'usuarios', localField: 'usuario_id', foreignField: '_id', as: 'usuario' },
      },
      { $unwind: '$usuario' },
      {
        $lookup: {
          from: 'acompanantes',
          localField: '_id',
          foreignField: 'inscripcion_id',
          as: 'acompanantes',
        },
      },
    ])
    .toArray();

  const filas: { tipo: string; nombre: string }[] = [];
  for (const i of inscripciones) {
    filas.push({ tipo: 'Socio', nombre: i.usuario.nombre });
    for (const a of i.acompanantes as { nombre: string }[]) {
      filas.push({ tipo: 'Acompañante', nombre: a.nombre });
    }
  }
  filas.sort((a, b) => a.nombre.localeCompare(b.nombre));

  return { destino: salida.destino as string, filas };
}
