@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-calendar')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <form method="POST" action="{{ route('admin-calendar-truncate') }}">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn btn-danger">Delete all events</button>
            </form>
        </div>
    @endsection

    <page-admin-calendar></page-admin-calendar>
@endsection
