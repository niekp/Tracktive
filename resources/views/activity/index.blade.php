@extends('layouts.master')
@section('title', 'Activiteiten')
@section('content')
    <div class="row row-cols-1 row-cols-md-2 g-4">

        @foreach ($activities as $activity)
            @php
                $data = $activity->getData();
            @endphp
            <div class="col">
                <div class="card">
                    <img class="card-img-top" src="{{ $activity->image }}" alt="Route">
                    <div class="card-body">
                        <h5 class="card-title">{{ $activity->type }}</h5>
                        <p class="card-text">
                            {{ $data->start->format('d-m-Y H:i') }} - {{ $data->stop->format('H:i') }}<br />
                            <table>
                                <tr><td>Afstand:</td><td>{{ $data->distance }} km</td></tr>
                                <tr><td>Snelheid:</td><td>{{ $data->average_speed_active }} km/u</td></tr>
                            </table>
                            <small class="text-muted">{{ $activity->persons->map(fn ($person) => $person->name)->join(', ') }}</small>
                        </p>
                        <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-primary">Details</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@endsection
