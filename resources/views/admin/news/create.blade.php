@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <h1>Post news</h1>

    <div class="bg-admin-dark p-4">
        <newscreate :categories="{{ $newsCategories }}"></newscreate>
    </div>
@endsection
