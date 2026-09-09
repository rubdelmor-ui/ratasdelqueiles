import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // "standalone" solo hace falta para la imagen Docker autoalojada; en
  // Vercel choca con su propio empaquetado (ENOENT next-server.js.nft.json).
  ...(process.env.BUILD_STANDALONE ? { output: "standalone" as const } : {}),
};

export default nextConfig;
