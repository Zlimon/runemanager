@extends('layouts.layout')

@section('title')
    Edit user
@endsection

@section('content')
    <page-user-edit :user="{{ $user }}" :random-icons="{{ json_encode($randomIcons) }}"></page-user-edit>
@endsection
