'use client';

import { useState } from 'react';
import { Sidebar, MobileHeader } from '@/components/Sidebar';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [mobileOpen, setMobileOpen] = useState(false);

  return (
    <div className="min-h-screen bg-slate-50">
      <MobileHeader onMenuClick={() => setMobileOpen(true)} />
      <Sidebar collapsed={!mobileOpen} onToggle={() => setMobileOpen(!mobileOpen)} />
      <main className="lg:ml-[260px] pt-16 lg:pt-0 p-4 lg:p-8 min-w-0 transition-all duration-300">
        {children}
      </main>
    </div>
  );
}
