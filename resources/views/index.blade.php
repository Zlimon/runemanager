@extends('layouts.layout')

@section('title')
    Home
@endsection

@section('content')
    <page-index :accounts="{{ $accounts }}" :news-posts="{{ $newsPosts }}"></page-index>
@endsection
