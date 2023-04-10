@extends('layouts.master')
@section('title','Nieuw')
@section('content')
    <a href="{{ route('activities.create') }}" class="btn btn-primary">Nieuwe activiteit</a>

    @foreach ($activities as $activity)
        <div class="card" style="width: 18rem;">
            <img class="card-img-top" src="..." alt="Route">
            <div class="card-body">
                <p class="card-text">
                    <ul>
                        <li>{{ $activity->date->format('d-m-Y H:i') }}</li>
                        <li>KM: ??</li>
                        <li>Snelheid: ??</li>
                    </ul>
                </p>
                <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-primary">Openen</a>
            </div>
        </div>
    @endforeach

@endsection
