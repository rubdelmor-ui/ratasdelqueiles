'use client'

import { useEffect } from 'react';
import { marcarVisitaActas } from '@/app/(main)/actas/actions';

/** Componente invisible que registra la visita a /actas (quita el punto rojo de aviso). */
export default function MarcarVisita() {
  useEffect(() => {
    marcarVisitaActas();
  }, []);

  return null;
}
