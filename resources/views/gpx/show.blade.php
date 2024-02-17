@extends('layouts.master')
@section('title', $activity->type . ' ' . $stats->start->format('d-m-Y H:i'))
@section('scripts')
    @include('_partials.map-scripts')
@endsection
@section('content')
    <h1>{{ $stats->start->format('d-m-Y H:i') }} - {{ $stats->stop->format('H:i') }}</h1>

    @include('_partials.map', [ 'activity' => $activity ])

    <div class="pb-5 pt-2">
        <a href="{{ route('gpx.create', ['index' => $index]) }}" class="btn btn-primary ms-2">Importeren</a>
        <a href="{{ route('gpx.index') }}" class="btn btn-secondary ms-2">Terug</a>
    </div>
@endsection
