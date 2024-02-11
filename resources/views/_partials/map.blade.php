<div style="width: {{ $activity->image ? '100%' : '640px' }}; height: 480px" id="mapContainer"></div>
<input type="hidden"
       data-configuration
       data-here-api-key="{{ Config::get('here.api_key') }}"
       data-here-app-id="{{ Config::get('here.app_id') }}"
       data-here-app-code="{{ Config::get('here.app_code') }}"
       data-capture-id="{{ $activity->image ? null : $activity->id }}"
/>

<div data-container="coordinates" style="display: none;">
    <activity>
        @foreach ($activity->getPoints() as $point)
            @if (!$point->active)
                @continue
            @endif
            <coord data-lat="{{ $point->latitude }}" data-long="{{ $point->longitude }}" data-time="{{ $point->time->format('c') }}"></coord>
        @endforeach
    </activity>
</div>
