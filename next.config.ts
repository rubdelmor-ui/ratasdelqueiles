import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // "standalone" solo hace falta para la imagen Docker autoalojada; en
  // Vercel choca con su propio empaquetado (ENOENT next-server.js.nft.json).
  ...(process.env.BUILD_STANDALONE ? { output: "standalone" as const } : {}),
  images: {
    remotePatterns: [
      { protocol: "https", hostname: "res.cloudinary.com" },
    ],
  },
  // Por defecto Next.js limita el cuerpo de una Server Action a 1MB, y
  // cualquier foto real de móvil (salidas, socios, home) lo supera de
  // sobra. Sin esto, subir una foto normal da "Body exceeded 1 MB limit".
  experimental: {
    serverActions: {
      bodySizeLimit: "15mb",
    },
  },
};

export default nextConfig;
