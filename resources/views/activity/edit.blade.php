@extends('layouts.master')
@section('title', 'Activiteit ' . $activity->data->start->format('d-m-Y H:i'))
@section('scripts')
    @include('_partials.map-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('/js/bootstrap-autocomplete.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#type').autocomplete()
        });
    </script>
@endsection
@section('content')
    @include('_partials.map', [ 'activity' => $activity ])

    <form method="post" action="{{ route('activities.update', $activity->id) }}" class="mt-3">
        @method('PATCH')
        @csrf

        <div class="form-group row">
            <label for="type" class="col-sm-2 col-form-label">Sport</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="type" id="type" value="{{ $activity->type }}" list="list-types" />

                <datalist id="list-types">
                    @foreach ($types as $type)
                        <option>{{ $type }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="form-group row mt-2">
            <label for="person" class="col-sm-2 col-form-label">Wie</label>
            <div class="col-sm-10">
                @foreach ($persons as $person)
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="person[]"
                               value="{{ $person->id }}"
                               id="person_{{ $person->id }}"
                               {{ $activity->persons->contains($person) ? "checked='checked'" : '' }}
                        />
                        <label class="form-check-label" for="person_{{ $person->id }}">{{ $person->name }}</label><br />
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-5">Opslaan</button>
    </form>

    <form method="POST"
          action="{{ route('activities.destroy', $activity->id) }}"
          style="float: left;"
          onsubmit="return confirm('Zekerweten?')"
    >
        @method ('DELETE')
        @csrf
        <button type="submit" class="btn btn-danger mt-5">Verwijderen</button>
    </form>
    <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-secondary ms-2 mt-5">Terug</a>
@endsection
