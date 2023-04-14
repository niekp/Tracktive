@extends('layouts.master')
@section('title','Sport')
@section('content')
    <div class="mb-3">
        <a href="{{ route('activities.create') }}" class="btn btn-primary">Nieuwe activiteit</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-4">

        @foreach ($activities as $activity)
            @php
                $data = $activity->getData();
            @endphp
            <div class="col">
                <div class="card">
                    <img class="card-img-top" src="{{ $activity->image }}" alt="Route">
                    <div class="card-body">
                        <p class="card-text">
                            <ul>
                                <li>{{ $data->start->format('d-m-Y H:i') }} - {{ $data->stop->format('H:i') }}</li>
                                <li>Afstand: {{ $data->distance }} km</li>
                                <li>Snelheid: {{ $data->average_speed_active }} km/u</li>
                            </ul>
                        </p>
                        <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-primary">Openen</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@endsection
