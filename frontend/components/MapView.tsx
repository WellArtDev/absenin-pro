'use client';

import { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import { Icon } from 'leaflet';
import { api } from '@/lib/api';
import 'leaflet/dist/leaflet.css';

const employeeIcon = new Icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
});

interface EmployeeLocation {
  employee_name: string;
  employee_code: string;
  gps_lat: number;
  gps_lng: number;
  clock_in: string;
  status: string;
  client_name?: string;
}

function MapUpdater({ locations }: { locations: EmployeeLocation[] }) {
  const map = useMap();
  useEffect(() => {
    if (locations.length > 0) {
      const bounds = locations.map((l) => [l.gps_lat, l.gps_lng] as [number, number]);
      map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
    }
  }, [locations, map]);
  return null;
}

export default function MapView() {
  const [locations, setLocations] = useState<EmployeeLocation[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchLocations = () => {
      api.get('/attendance/locations')
        .then((r) => setLocations(r.data || []))
        .catch(() => {})
        .finally(() => setLoading(false));
    };
    fetchLocations();
    const interval = setInterval(fetchLocations, 30000);
    return () => clearInterval(interval);
  }, []);

  if (loading) return <div className="bg-white rounded-xl shadow-sm h-[400px] animate-pulse" />;
  if (locations.length === 0) {
    return (
      <div className="bg-white rounded-xl shadow-sm p-8 text-center h-[400px] flex items-center justify-center">
        <div>
          <div className="text-4xl mb-3 opacity-30">🗺️</div>
          <p className="text-slate-400">Belum ada karyawan aktif dengan live location</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
      <div className="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
        <h3 className="font-semibold text-sm">Live Location · {locations.length} karyawan aktif</h3>
        <span className="text-xs text-slate-400">Auto-refresh 30s</span>
      </div>
      <MapContainer
        center={[-6.2088, 106.8456]}
        zoom={12}
        className="h-[400px] w-full"
        scrollWheelZoom={true}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <MapUpdater locations={locations} />
        {locations.map((l, i) => (
          <Marker key={i} position={[l.gps_lat, l.gps_lng]} icon={employeeIcon}>
            <Popup>
              <div className="text-sm">
                <strong>{l.employee_name}</strong><br />
                <span className="text-slate-500 text-xs">{l.employee_code}</span><br />
                <span className="text-green-600 text-xs">Clock In: {l.clock_in?.substring(11, 16)}</span>
                {l.client_name && <><br /><span className="text-blue-600 text-xs">{l.client_name}</span></>}
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>
    </div>
  );
}
