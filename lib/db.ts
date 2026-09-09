import getClientPromise from './mongodb';

export const DB_NAME = 'ratas_queiles';

export async function getDb() {
  const client = await getClientPromise();
  return client.db(DB_NAME);
}
