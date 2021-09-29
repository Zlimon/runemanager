@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <a class="btn btn-success" href="{{ route('admin-account-create') }}">Register account</a>
        </div>
    @endsection

    <page-admin-account-index :accounts="{{ $accounts }}"></page-admin-account-index>
@endsection
