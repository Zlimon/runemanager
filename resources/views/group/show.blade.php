@extends('layouts.layout')

@section('title')
    {{ $group->name }}
@endsection

@section('content')
    <page-group-show :group="{{ $group }}"></page-group-show>
@endsection
