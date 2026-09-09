import Image from 'next/image';

export default function AvatarSocio({ foto, size = 56 }: { foto: string | null; size?: number }) {
  if (foto) {
    return (
      <Image
        src={foto}
        alt="Foto"
        width={size}
        height={size}
        className="rounded-full object-cover border-2 border-steel/60 bg-surface-high flex-shrink-0"
      />
    );
  }
  return (
    <div
      style={{ width: size, height: size, fontSize: size * 0.45 }}
      className="rounded-full border-2 border-steel/60 bg-surface-high flex items-center justify-center flex-shrink-0"
    >
      <span className="material-symbols-outlined text-ash" style={{ fontSize: size * 0.5 }}>
        person
      </span>
    </div>
  );
}
