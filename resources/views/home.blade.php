@extends('layouts.layout')

@section('title')
    {{ Auth::user()->name }}
@endsection

@section('content')
    <page-home :user="{{ Auth::user() }}"></page-home>
@endsection
