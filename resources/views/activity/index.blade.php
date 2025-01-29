@extends('layouts.master')
@section('title', 'Activiteiten')

@section('scripts')
    <script src="{{ asset('/js/activities.js') }}" type="text/javascript" charset="utf-8"></script>
@endsection

@section('content')
    @if (!$selected_persons && !$selected_type)
        <a class="btn btn-primary mb-2" data-bs-toggle="collapse" href="#filter" role="button" aria-expanded="false" aria-controls="filter" onclick="this.style.display = 'none';">Filteren</a>
        <div class="row collapse multi-collapse" id="filter">
    @else
        <div class="row">
    @endif
        <form method="get">
            <form class="form-inline">
                <div class="form-group mb-2">
                    <label class="col-sm-1">Wie</label>
                    @foreach ($persons as $person)
                        <span class="ms-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="person[]"
                                   value="{{ $person->id }}"
                                   id="person_{{ $person->id }}"
                                  {{ in_array($person->id, $selected_persons ?? []) ? "checked='checked'" : '' }}
                            />
                            <label class="form-check-label" for="person_{{ $person->id }}">{{ $person->name }}</label>
                        </span>
                    @endforeach
                </div>

                <div class="row g-3 align-items-center">
                    <label class="col-sm-1">Activiteit</label>
                    <div class="col-auto ms-2">
                        <select name="type" class="form-control">
                            <option value="">-</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}"
                                {{ $selected_type === $type ? "selected='selected'" : '' }}
                                >{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 align-items-center">
                    <label class="col-sm-1">Favoriet</label>
                    <div class="col-auto ms-2">
                        <span>
                            <input class="form-check-input"
                                type="checkbox"
                                name="favorite"
                                value="1"
                                id="favorite"
                                {{ $favorite ? "checked='checked'" : '' }}
                            />
                        </span>
                    </div>
                </div>

                <div class="row g-3 align-items-center">
                    <label class="col-sm-1"></label>
                    <div class="col-auto ms-2">
                        <button type="submit" class="btn btn-primary my-2">Filter</button>
                        <a href="{{ route('activities.index') }}" class="btn btn-secondary">Annuleren</a>
                    </div>
                </div>
            </form>
        </form>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
        @foreach ($activities as $activity)
            @php
                $data = $activity->getData();
            @endphp
            <div class="col">
                <div class="card mb-3">                    
                    <div class="position-relative">
                        <img class="card-img-top" src="{{ $activity->image }}" alt="Route">
                        <i
                            class="fas fa-star position-absolute top-0 end-0 m-3"
                            data-favorite-id="{{ $activity->id }}"
                            data-favorite="{{$activity->favorite}}"
                            style="color: {{ $activity->favorite ? 'gold' : 'gray' }};"
                        ></i>
                    </div>

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
    {{ $activities->links('pagination::bootstrap-4') }}
@endsection
