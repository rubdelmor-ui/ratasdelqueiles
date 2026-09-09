import Header from '@/components/Header';
import Sidebar from '@/components/Sidebar';
import BottomNav from '@/components/BottomNav';
import { getNavContext } from '@/lib/nav';

export default async function MainLayout({ children }: { children: React.ReactNode }) {
  const nav = await getNavContext();

  return (
    <>
      <Header session={nav.session} />
      <Sidebar {...nav} />
      <main className="flex-grow px-4 pt-4 md:ml-64 flex flex-col gap-4 pb-28 md:pb-10 max-w-[1200px] w-full mx-auto">
        {children}
      </main>
      <BottomNav {...nav} />
    </>
  );
}
