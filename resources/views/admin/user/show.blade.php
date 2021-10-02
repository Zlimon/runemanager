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
            <a class="btn btn-primary" href="{{ route('admin-user-edit', $user->id) }}">Edit</a>
        </div>

        <div class="p-2">
            <a class="btn btn-danger" href="">Ban</a>
        </div>
    @endsection

    <page-admin-user-show :user="{{ $user }}"></page-admin-user-show>
@endsection
