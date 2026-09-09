import Image from 'next/image';
import type { SessionPayload } from '@/lib/session';
import SettingsMenu from './SettingsMenu';

export default function Header({ session }: { session: SessionPayload | null }) {
  return (
    <header className="sticky top-0 z-40 flex items-center justify-between w-full h-16 px-4 bg-asphalt/90 backdrop-blur-md border-b-2 border-steel/60">
      <div className="flex items-center gap-2.5">
        <div className="relative w-9 h-9 rounded-full border-2 border-rust/70 p-[2px] shadow-[0_0_0_2px_rgba(0,0,0,0.4)]">
          <Image
            alt="Ratas del Queiles"
            className="w-full h-full object-cover rounded-full"
            src="/images/logo2.jpg"
            width={36}
            height={36}
            priority
          />
        </div>
        <h1 className="display-text text-[17px] text-chrome leading-none">
          Ratas <span className="text-rust">del Queiles</span>
        </h1>
      </div>
      <SettingsMenu session={session} />
    </header>
  );
}
