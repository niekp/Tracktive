@extends('layouts.master')
@section('title', $activity->type . ' ' . $stats->start->format('d-m-Y H:i'))
@section('scripts')
@include('_partials.map-scripts')
@endsection
@section('content')
<h1>{{ $stats->start->format('d-m-Y H:i') }} - {{ $stats->stop->format('H:i') }}</h1>

@include('_partials.map', [ 'activity' => $activity ])

<h1>Data</h1>
<div class="form-group row">
    <label for="distance" class="col-sm-2 col-form-label">Afstand</label>
    <div class="col-sm-10">
        <input type="text" readonly class="form-control-plaintext" id="distance" value="{{ $stats->distance }} km">
    </div>
</div>
<div class="form-group row">
    <label for="speed" class="col-sm-2 col-form-label">Gemiddelde snelheid</label>
    <div class="col-sm-10">
        <input type="text" readonly class="form-control-plaintext" id="speed" value="{{ $stats->average_speed_total }} km/u">
    </div>
</div>

<div class="pb-5 pt-2">
    <a href="{{ route('gpx.create', ['index' => $index]) }}" class="btn btn-primary ms-2">Importeren</a>
    <a href="{{ route('gpx.index') }}" class="btn btn-secondary ms-2">Terug</a>
</div>
@endsection
