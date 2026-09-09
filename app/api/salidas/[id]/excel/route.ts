import { NextRequest, NextResponse } from 'next/server';
import { requireSuperadmin } from '@/lib/session';
import { getAsistentesParaExcel } from '@/lib/salidas';

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

export async function GET(_req: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  await requireSuperadmin('/salidas');
  const { id } = await params;

  const datos = await getAsistentesParaExcel(id);
  if (!datos) {
    return NextResponse.json({ error: 'Salida no encontrada' }, { status: 404 });
  }

  const filas = [['Tipo', 'Nombre'], ...datos.filas.map((f) => [f.tipo, f.nombre])];
  if (datos.filas.length === 0) {
    filas.push(['No hay asistentes apuntados a esta salida.']);
  }

  const bom = '﻿';
  const csv = bom + aCsv(filas);
  const nombreArchivo = `Asistentes_${datos.destino.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date()
    .toISOString()
    .slice(0, 10)}.xls`;

  return new NextResponse(csv, {
    headers: {
      'Content-Type': 'text/csv; charset=utf-8',
      'Content-Disposition': `attachment; filename="${nombreArchivo}"`,
    },
  });
}
