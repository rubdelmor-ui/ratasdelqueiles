// lib/mongodb.ts
import { MongoClient } from 'mongodb';

const options = {};
let cached: Promise<MongoClient> | undefined;

/**
 * Conexión perezosa: no se evalúa nada al importar el módulo, solo cuando
 * alguien realmente pide la conexión. Evita que `next build` reviente si
 * MONGODB_URI no está disponible en tiempo de build (p.ej. en Docker).
 */
export default function getClientPromise(): Promise<MongoClient> {
  const uri = process.env.MONGODB_URI;
  if (!uri) {
    throw new Error('Añade tu Mongo URI en el archivo .env.local');
  }

  if (process.env.NODE_ENV === 'development') {
    const globalWithMongo = global as typeof globalThis & {
      _mongoClientPromise?: Promise<MongoClient>;
    };
    if (!globalWithMongo._mongoClientPromise) {
      globalWithMongo._mongoClientPromise = new MongoClient(uri, options).connect();
    }
    return globalWithMongo._mongoClientPromise;
  }

  if (!cached) {
    cached = new MongoClient(uri, options).connect();
  }
  return cached;
}
