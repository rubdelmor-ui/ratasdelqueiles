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

interface InscripcionConDatos {
  salida_id: ObjectId;
  usuario_id: ObjectId;
  usuario: { nombre: string };
  acompanantes: { nombre: string }[];
}

/** Replica las consultas de salidas.php: próximas salidas + conteo y listado de
 * asistentes (socios + acompañantes) para cada una. Una sola consulta agregada
 * para todas las salidas a la vez, en vez de una por salida (N+1). */
export async function getSalidasConDatos(usuarioId?: string): Promise<SalidaConDatos[]> {
  const db = await getDb();
  const hoy = new Date().toISOString().slice(0, 10);

  const salidas = await db
    .collection('salidas')
    .find({ fecha_salida: { $gte: hoy } })
    .sort({ fecha_salida: 1 })
    .toArray();

  if (salidas.length === 0) return [];

  const salidaIds = salidas.map((s) => s._id);

  const inscripciones = await db
    .collection('inscripciones')
    .aggregate<InscripcionConDatos>([
      { $match: { salida_id: { $in: salidaIds } } },
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

  const porSalida = new Map<string, InscripcionConDatos[]>();
  for (const insc of inscripciones) {
    const key = insc.salida_id.toString();
    const lista = porSalida.get(key);
    if (lista) lista.push(insc);
    else porSalida.set(key, [insc]);
  }

  return salidas.map((salida) => {
    const inscripcionesSalida = porSalida.get(salida._id.toString()) ?? [];

    const listaAsistentes: AsistenteConAcompanantes[] = inscripcionesSalida.map((i) => ({
      socio: i.usuario.nombre,
      acompanantes: i.acompanantes.map((a) => a.nombre).sort((a, b) => a.localeCompare(b)),
    }));

    const totalInscritos = listaAsistentes.reduce((acc, a) => acc + 1 + a.acompanantes.length, 0);

    const yaApuntado = usuarioId
      ? inscripcionesSalida.some((i) => i.usuario_id.toString() === usuarioId)
      : false;

    let fechaIso = '';
    if (salida.fecha_salida && salida.hora_quedada) {
      const dt = new Date(`${salida.fecha_salida}T${salida.hora_quedada}`);
      if (!isNaN(dt.getTime())) fechaIso = dt.toISOString();
    }

    return {
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
    };
  });
}

export interface SalidaPasada {
  id: string;
  destino: string;
  fechaSalida: string;
  totalFotos: number;
}

/** Salidas ya celebradas (más recientes primero), para poder seguir viendo y
 * subiendo sus fotos cuando ya no salen en "próximas". */
export async function getSalidasPasadas(): Promise<SalidaPasada[]> {
  const db = await getDb();
  const hoy = new Date().toISOString().slice(0, 10);

  const [salidas, conteos] = await Promise.all([
    db.collection('salidas').find({ fecha_salida: { $lt: hoy } }).sort({ fecha_salida: -1 }).toArray(),
    db.collection('fotos_salidas').aggregate([{ $group: { _id: '$salida_id', total: { $sum: 1 } } }]).toArray(),
  ]);
  const totales = new Map(conteos.map((c) => [String(c._id), c.total as number]));

  return salidas.map((s) => ({
    id: s._id.toString(),
    destino: s.destino as string,
    fechaSalida: s.fecha_salida as string,
    totalFotos: totales.get(s._id.toString()) ?? 0,
  }));
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
