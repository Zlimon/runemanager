@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <newscreate :categories="{{ $newsCategories }}"></newscreate>
@endsection
