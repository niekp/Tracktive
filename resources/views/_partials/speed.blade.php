<div>
    <canvas id="speed-chart"></canvas>
</div>

<div data-container="speeds" style="display: none;">
    <activity>
        @foreach ($activity->getData()->points as $speed)
            <speed data-speed="{{ $speed->speed }}" data-pace="{{ $speed->pace }}" data-hr="{{ $speed->heart_rate }}" data-time="{{ $speed->time->format('c') }}"></speed>
        @endforeach
    </activity>
</div>

