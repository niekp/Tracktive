@extends('layouts.master')
@section('title','Nieuw')
@section('content')

    <form method="post" action="{{ route('activities.store') }}" enctype='multipart/form-data'>
        @csrf

        <div class="mb-3">
            <label for="formFile" class="form-label">GPX</label>
            <input class="form-control" type="file" name="gpx" />
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>

@endsection
