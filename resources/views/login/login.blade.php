@extends('layouts.anonymous')
@section('title', 'Login')
@section('content')
    <form method="post" action="{{ route('login') }}">
        @csrf

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Request login</button>
        </div>
    </form>

@endsection
