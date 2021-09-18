@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <h1>Post news</h1>

    <newscreate :categories="{{ $newsCategories }}"></newscreate>
@endsection
