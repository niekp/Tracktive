@extends('layouts.master')
@section('title', 'Activiteiten')
@section('scripts')
@include('_partials.map-scripts')
@endsection
@section('content')
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
    @foreach ($activities as $key => $activity)
    @php
    $data = $activity->getData();
    @endphp
    <div class="col">
        <div class="card mb-3">
            <div class="card-img-top" alt="Route">
                @include('_partials.map', [ 'activity' => $activity ])
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $activity->type }}</h5>
                <p class="card-text">
                    {{ $data->start->format('d-m-Y H:i') }} - {{ $data->stop->format('H:i') }}<br />
                </p>
                <a href="{{ route('gpx.show', $key) }}" class="btn btn-primary">Bekijken</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
