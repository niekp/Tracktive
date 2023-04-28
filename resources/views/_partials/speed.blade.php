<div>
    <canvas id="speed-chart"></canvas>
</div>

<div data-container="speeds" style="display: none;">
    <activity>
        @foreach ($activity->getData()->speeds as $speed)
            <speed data-speed="{{ $speed->speed }}" data-time="{{ $speed->time->format('c') }}"></speed>
        @endforeach
    </activity>
</div>

