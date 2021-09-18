@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-calendar')
    active
@endsection

@section('content')
    @section('navigation')
        <form method="POST" action="{{ route('admin-calendar-truncate') }}">
            @method('DELETE')
            @csrf

            <button type="submit" class="btn btn-danger">Delete all events</button>
        </form>
    @endsection

    <calendaredit></calendaredit>
@endsection
