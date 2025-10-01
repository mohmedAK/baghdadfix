@php
    $record = $getRecord();

    // Fallback to Baghdad if coords are null or on create
    $lat  = is_null($record?->gps_lat) ? 33.3152 : (float) $record->gps_lat;
    $lng  = is_null($record?->gps_lng) ? 44.3661 : (float) $record->gps_lng;
    $zoom = 14;

    $mapId = 'map_' . uniqid();
@endphp

<div x-data x-init="window.initLeaflet(@js($mapId), @js($lat), @js($lng), @js($zoom))">
    <div id="{{ $mapId }}" style="height:320px;border-radius:.5rem;overflow:hidden;"></div>
</div>

@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script>
        // One-time loader for Leaflet
        window.__leafletLoader = window.__leafletLoader || new Promise((resolve) => {
            if (window.L) return resolve();
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            s.onload = () => resolve();
            document.head.appendChild(s);
        });

        // Simple global init to keep x-init short and safe
        window.initLeaflet = async function (mapId, lat, lng, zoom) {
            await window.__leafletLoader;

            const el = document.getElementById(mapId);
            if (!el) return;

            const map = L.map(el).setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            L.marker([lat, lng]).addTo(map);

            // Fix gray tiles when inside tabs/panels
            setTimeout(() => map.invalidateSize(), 100);
            window.addEventListener('resize', () => map.invalidateSize());
        };
    </script>
@endonce
