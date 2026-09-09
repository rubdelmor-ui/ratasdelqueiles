import { NextResponse } from 'next/server';
import { getDb } from '@/lib/db';
import { requireSuperadmin } from '@/lib/session';
import type { Usuario } from '@/lib/types';

function aCsv(filas: string[][]): string {
  return filas
    .map((fila) =>
      fila
        .map((campo) => {
          const escapado = campo.replace(/"/g, '""');
          return /[;"\n]/.test(escapado) ? `"${escapado}"` : escapado;
        })
        .join(';')
    )
    .join('\r\n');
}

export async function GET() {
  await requireSuperadmin('/socios');

  const db = await getDb();
  const usuarios = await db
    .collection<Usuario>('usuarios')
    .find({})
    .sort({ fecha_registro: -1 })
    .toArray();

  const filas = [
    ['ID', 'Nombre', 'Email', 'Rol', 'Cargo', 'Estado', 'Fecha Registro'],
    ...usuarios.map((u) => [
      u._id.toString(),
      u.nombre,
      u.email,
      u.rol.toUpperCase(),
      u.cargo || '',
      u.aprobado === 1 ? 'Aprobado' : 'Pendiente',
      new Date(u.fecha_registro).toLocaleDateString('es-ES'),
    ]),
  ];

  const bom = '﻿';
  const csv = bom + aCsv(filas);
  const nombreArchivo = `socios_${new Date().toISOString().slice(0, 10)}.xls`;

  return new NextResponse(csv, {
    headers: {
      'Content-Type': 'text/csv; charset=utf-8',
      'Content-Disposition': `attachment; filename="${nombreArchivo}"`,
    },
  });
}
