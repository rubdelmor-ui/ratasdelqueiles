import type { ObjectId } from 'mongodb';

export interface Usuario {
  _id: ObjectId;
  nombre: string;
  email: string;
  password: string;
  rol: 'socio' | 'junta';
  cargo?: string;
  aprobado: 0 | 1;
  foto: string | null;
  pregunta_seguridad: string;
  respuesta_seguridad: string;
  fecha_registro: Date;
}

export interface Salida {
  _id: ObjectId;
  destino: string;
  fecha_salida: string; // 'YYYY-MM-DD'
  hora_quedada: string; // 'HH:MM'
  punto_encuentro: string;
  descripcion: string;
  imagen: string | null;
  responsable: string;
  fecha_creacion: Date;
}

export interface Inscripcion {
  _id: ObjectId;
  salida_id: ObjectId;
  usuario_id: ObjectId;
  fecha_inscripcion: Date;
}

export interface Acompanante {
  _id: ObjectId;
  inscripcion_id: ObjectId;
  nombre: string;
}

export interface Acta {
  _id: ObjectId;
  titulo: string;
  fecha_reunion: string;
  autor: string;
  firmas: number;
  archivo_pdf: string | null;
  texto_acta: string;
  fecha_creacion: Date;
}

export interface Configuracion {
  _id: ObjectId;
  clave: string;
  valor: string;
}

export interface ContenidoHome {
  _id: ObjectId;
  seccion: 'bienvenida';
  contenido: string;
  imagen: string | null;
  texto_imagen: string;
}
