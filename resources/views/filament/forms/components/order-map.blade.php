@php
    // Unique DOM id so multiple maps on the page don’t clash
    $mapId = 'map_' . uniqid();
@endphp

@once
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endonce

<div id="{{ $mapId }}" style="height: 320px; border-radius: .5rem; overflow: hidden;"></div>

<script>
    (function () {
        const el   = document.getElementById(@json($mapId));
        const lat  = @json($lat);
        const lng  = @json($lng);
        const zoom = @json($zoom ?? 14);

        // Build the map
        const map = L.map(el).setView([lat, lng], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        // Marker
        L.marker([lat, lng]).addTo(map);
    })();
</script>
