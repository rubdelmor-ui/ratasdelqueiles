import './globals.css'
import ServiceWorkerRegister from '@/components/ServiceWorkerRegister'

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
    <html lang="es" className="dark">
      <head>
        <link rel="manifest" href="/manifest.json" />
        <meta name="theme-color" content="#121110" />
        <link rel="apple-touch-icon" href="/images/logo2.jpg" />
        <link
          href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap"
          rel="stylesheet"
        />
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
