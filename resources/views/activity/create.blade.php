@extends('layouts.master')
@section('title', 'Nieuwe activiteit')
@section('content')

    <form method="post" action="{{ route('activities.store') }}" enctype='multipart/form-data'>
        @csrf

        <div class="mb-3">
            <label for="formFile" class="form-label">GPX</label>
            <input class="form-control" type="file" name="gpx[]" multiple />
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Upload</button>
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">Annuleren</a>
        </div>
    </form>

@endsection
