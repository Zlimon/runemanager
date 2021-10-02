@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-users')
    active
@endsection

@section('content')
    <page-admin-user-index :users="{{ $users }}"></page-admin-user-index>
@endsection
