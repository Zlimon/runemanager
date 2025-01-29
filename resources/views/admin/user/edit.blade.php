@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-users')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <a class="btn btn-success" href="{{ route('admin-user-show', $user->id) }}">Show</a>
        </div>

        <div class="p-2">
            <a class="btn btn-danger" href="">Ban</a>
        </div>
    @endsection

    <page-admin-user-edit :user="{{ $user }}"></page-admin-user-edit>
@endsection
