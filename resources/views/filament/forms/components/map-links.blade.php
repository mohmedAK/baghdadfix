@php
    $record = $getRecord();
    $lat = is_null($record?->gps_lat) ? 33.3152 : (float) $record->gps_lat;
    $lng = is_null($record?->gps_lng) ? 44.3661 : (float) $record->gps_lng;
    $zoom = 16;

    $googlePlace = "https://www.google.com/maps?q={$lat},{$lng}";
    $googleDirections = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
    $openStreetMap = "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lng}#map={$zoom}/{$lat}/{$lng}";
    $waze = "https://waze.com/ul?ll={$lat},{$lng}&navigate=yes";
    $appleMaps = "https://maps.apple.com/?ll={$lat},{$lng}";
@endphp

<div class="flex flex-wrap gap-2">
    <x-filament::button tag="a" href="{{ $googlePlace }}" target="_blank" size="sm" icon="heroicon-m-map-pin">
        Google Maps
    </x-filament::button>

    <x-filament::button tag="a" href="{{ $googleDirections }}" target="_blank" size="sm"
        icon="heroicon-m-map-pin">
        Directions
    </x-filament::button>

    <x-filament::button tag="a" href="{{ $openStreetMap }}" target="_blank" size="sm">
        OpenStreetMap
    </x-filament::button>

    <x-filament::button tag="a" href="{{ $waze }}" target="_blank" size="sm">
        Waze
    </x-filament::button>

    <x-filament::button tag="a" href="{{ $appleMaps }}" target="_blank" size="sm">
        Apple Maps
    </x-filament::button>
</div>
