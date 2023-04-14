<div style="width: 640px; height: 480px" id="mapContainer"></div>
<input type="hidden"
       data-configuration
       data-here-api-key="{{ Config::get('here.api_key') }}"
       data-here-app-id="{{ Config::get('here.app_id') }}"
       data-here-app-code="{{ Config::get('here.app_code') }}"
       data-capture-id="{{ $capture_id ?? null }}"
/>
