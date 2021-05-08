@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <h1>{{ __('title.create-newspost') }}</h1>

    <newscreate :categories="{{ $newsCategories }}"></newscreate>
@endsection
