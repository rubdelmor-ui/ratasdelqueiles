import './globals.css'
import { Anybody, Hanken_Grotesk, JetBrains_Mono } from 'next/font/google'
import ServiceWorkerRegister from '@/components/ServiceWorkerRegister'

// Autoalojadas con next/font: se descargan en build, sin petición externa a
// Google Fonts en el navegador (menos bloqueo de render, cero CLS por FOUT).
const anybody = Anybody({
  subsets: ['latin'],
  weight: ['600', '700', '800', '900'],
  variable: '--font-anybody',
  display: 'swap',
})
const hankenGrotesk = Hanken_Grotesk({
  subsets: ['latin'],
  weight: ['400', '500', '600', '700'],
  variable: '--font-hanken',
  display: 'swap',
})
const jetbrainsMono = JetBrains_Mono({
  subsets: ['latin'],
  weight: ['500', '700'],
  variable: '--font-jetbrains',
  display: 'swap',
})

export const metadata = {
  title: 'Ratas del Queiles',
  description: 'Club de motos',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html
      lang="es"
      className={`dark ${anybody.variable} ${hankenGrotesk.variable} ${jetbrainsMono.variable}`}
    >
      <head>
        <link rel="manifest" href="/manifest.json" />
        <meta name="theme-color" content="#121110" />
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png" />
        <link rel="icon" href="/icons/icon-192.png" type="image/png" sizes="192x192" />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="bg-asphalt text-chrome min-h-screen flex flex-col noise-bg antialiased">
        <div className="road-glow" />
        <div className="relative z-[1] flex flex-col min-h-screen">{children}</div>
        <ServiceWorkerRegister />
      </body>
    </html>
  )
}
