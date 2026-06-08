'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

const menuItems = [
  { href: '/dashboard', label: 'Dashboard', icon: '\u{1F3E0}' },
  { href: '/dashboard/employees', label: 'Karyawan', icon: '\u{1F465}' },
  { href: '/dashboard/attendance', label: 'Presensi', icon: '\u{1F4CB}' },
  { href: '/dashboard/overtime', label: 'Lembur', icon: '\u{23F0}' },
  { href: '/dashboard/leaves', label: 'Cuti & Izin', icon: '\u{1F4DD}' },
  { href: '/dashboard/reports', label: 'Report', icon: '\u{1F4CA}' },
  { href: '/dashboard/clients', label: 'Klien', icon: '\u{1F4CD}' },
  { href: '/dashboard/settings', label: 'Pengaturan', icon: '\u{2699}\u{FE0F}' },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-[260px] bg-white border-r border-slate-200 fixed top-0 left-0 bottom-0 flex flex-col z-10">
      <div className="px-6 py-5 border-b border-slate-200">
        <h2 className="text-xl font-semibold text-primary-600">Absenin</h2>
      </div>
      <nav className="flex-1 p-3 overflow-y-auto">
        {menuItems.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors mb-0.5 ${
              pathname === item.href
                ? 'bg-primary-50 text-primary-700 font-medium'
                : 'text-slate-600 hover:bg-slate-50'
            }`}
          >
            <span className="text-base">{item.icon}</span>
            {item.label}
          </Link>
        ))}
      </nav>
      <div className="px-6 py-4 border-t border-slate-200 text-sm text-slate-500">
        HR Dashboard
      </div>
    </aside>
  );
}
