@extends('layouts.master')
@section('title', $activity->type . ' ' . $stats->start->format('d-m-Y H:i'))
@section('scripts')
    @include('_partials.map-scripts')
    @include('_partials.speed-scripts')
@endsection
@section('content')
    <h1>{{ $activity->type }} op {{ $stats->start->format('d-m-Y H:i') }} - {{ $stats->stop->format('H:i') }}</h1>
    <div class="mb-2"><small class="text-muted">{{ $activity->persons->map(fn ($person) => $person->name)->join(', ') }}</small></div>

    @include('_partials.map', [ 'activity' => $activity ])

    @include('_partials.speed', [ 'activity' => $activity ])

    <h1>Data</h1>
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
    @if ($stats->seconds_paused < 30 * 60)
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
    @endif
    @if ($stats->seconds_paused)
        <div class="form-group row">
            <label for="duration" class="col-sm-2 col-form-label">Actieve tijd</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="duration" value="{{ gmdate('H:i:s', $stats->seconds_active) }}">
            </div>
        </div>
    @endif

    <div class="pb-5 pt-2">
        <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-primary">Bewerken</a>
        <a href="{{ route('activities.download', $activity->id) }}" class="btn btn-secondary ms-2">Download</a>
        <a href="{{ route('activities.index') }}" class="btn btn-secondary ms-2">Terug</a>
    </div>
@endsection
