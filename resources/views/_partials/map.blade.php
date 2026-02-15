@php
    $id = Str::uuid()
@endphp
<div class="mapContainer" style="width: {{ $activity->image ? '100%' : '640px' }}; height: 480px" id="mapContainer{{ $id }}"></div>
<input type="hidden"
       data-configuration
       data-capture-id="{{ $activity->image ? null : $activity->id }}"
/>

<div data-container="coordinates" data-map-container="{{ $id }}" style="display: none;">
    <activity>
        @foreach ($activity->getPoints() as $point)
            @if (!$point->active)
                @continue
            @endif
            <coord data-lat="{{ $point->latitude }}" data-long="{{ $point->longitude }}" data-time="{{ $point->time->format('c') }}"></coord>
        @endforeach
    </activity>
</div>
