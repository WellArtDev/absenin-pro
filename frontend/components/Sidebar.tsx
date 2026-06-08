'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useState } from 'react';
import { LayoutDashboard, Users, ClipboardList, Clock, FileText, BarChart3, MapPin, Settings, Menu, X, ChevronLeft } from 'lucide-react';

const menuItems = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/dashboard/employees', label: 'Karyawan', icon: Users },
  { href: '/dashboard/attendance', label: 'Presensi', icon: ClipboardList },
  { href: '/dashboard/overtime', label: 'Lembur', icon: Clock },
  { href: '/dashboard/leaves', label: 'Cuti & Izin', icon: FileText },
  { href: '/dashboard/reports', label: 'Report', icon: BarChart3 },
  { href: '/dashboard/clients', label: 'Klien', icon: MapPin },
  { href: '/dashboard/settings', label: 'Pengaturan', icon: Settings },
];

export function Sidebar({ collapsed, onToggle }: { collapsed: boolean; onToggle: () => void }) {
  const pathname = usePathname();

  return (
    <>
      {!collapsed && <div className="fixed inset-0 bg-black/50 z-20 lg:hidden" onClick={onToggle} />}
      <aside className={`fixed top-0 left-0 bottom-0 z-30 bg-white border-r border-slate-200 flex flex-col transition-all duration-300 ${collapsed ? '-translate-x-full lg:translate-x-0 lg:w-[72px]' : 'w-[260px]'}`}>
        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-200">
          {!collapsed && <h2 className="text-lg font-bold text-primary-600">Absenin</h2>}
          <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 lg:block hidden">
            <ChevronLeft size={18} className={`transition-transform ${collapsed ? 'rotate-180' : ''}`} />
          </button>
          <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 lg:hidden">
            <X size={18} />
          </button>
        </div>
        <nav className="flex-1 p-2 overflow-y-auto">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const active = pathname === item.href || (item.href !== '/dashboard' && pathname?.startsWith(item.href));
            return (
              <Link key={item.href} href={item.href} onClick={() => { if (window.innerWidth < 1024) onToggle(); }}
                className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors mb-0.5 ${active ? 'bg-primary-50 text-primary-700 font-medium' : 'text-slate-600 hover:bg-slate-50'} ${collapsed ? 'lg:justify-center lg:px-2' : ''}`}>
                <Icon size={20} className="shrink-0" />
                {!collapsed && <span>{item.label}</span>}
              </Link>
            );
          })}
        </nav>
      </aside>
    </>
  );
}

export function MobileHeader({ onMenuClick }: { onMenuClick: () => void }) {
  return (
    <div className="lg:hidden fixed top-0 left-0 right-0 z-10 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
      <button onClick={onMenuClick} className="p-2 -ml-2 rounded-lg hover:bg-slate-100"><Menu size={22} /></button>
      <h2 className="text-lg font-bold text-primary-600">Absenin</h2>
      <div className="w-10" />
    </div>
  );
}
