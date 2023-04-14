@extends('layouts.master')
@section('title','Nieuw')
@section('scripts')
    @include('_partials.map-scripts')
@endsection
@section('content')
    @include('_partials.map', [ 'capture_id' => $activity->id])

    <div class="form-group row">
        <label for="distance" class="col-sm-2 col-form-label">Afstand</label>
        <div class="col-sm-10">
            <input type="text" readonly class="form-control-plaintext" id="distance" value="{{ $stats->distance }} km">
        </div>
    </div>
    @if ($stats->seconds_paused)
        <div class="form-group row">
            <label for="speed" class="col-sm-2 col-form-label">Snelheid (actief)</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="speed" value="{{ $stats->average_speed_active }} km/u">
            </div>
        </div>
    @endif
    <div class="form-group row">
        <label for="speed" class="col-sm-2 col-form-label">Gemiddelde snelheid</label>
        <div class="col-sm-10">
            <input type="text" readonly class="form-control-plaintext" id="speed" value="{{ $stats->average_speed_total }} km/u">
        </div>
    </div>
    <div class="form-group row">
        <label for="duration" class="col-sm-2 col-form-label">Tijdsduur</label>
        <div class="col-sm-10">
            <input type="text" readonly class="form-control-plaintext" id="duration" value="{{ gmdate('H:i:s', $stats->seconds_active + $stats->seconds_paused) }}">
        </div>
    </div>
    @if ($stats->seconds_paused)
        <div class="form-group row">
            <label for="duration" class="col-sm-2 col-form-label">Waarvan actief</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="duration" value="{{ gmdate('H:i:s', $stats->seconds_active) }}">
            </div>
        </div>
    @endif
    <div data-container="coordinates" style="display: none;">
        <activity>
            @foreach ($stats->coordinates as $coordinate)
                <coord data-lat="{{ $coordinate->latitude }}" data-long="{{ $coordinate->longitude }}" data-time="{{ $coordinate->time }}"></coord>
            @endforeach
        </activity>
    </div>

    <form method="POST"
          action="{{ route('activities.destroy', $activity->id) }}"
          style="float: left;"
          onsubmit="return confirm('Zekerweten?')"
    >
        @method ('DELETE')
        @csrf
        <button type="submit" class="btn btn-danger mt-5">Verwijderen</button>
    </form>
    <a href="{{ route('activities.index') }}" class="btn btn-secondary ms-2 mt-5">Terug</a>
@endsection
