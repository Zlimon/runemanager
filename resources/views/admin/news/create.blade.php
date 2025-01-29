@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <page-admin-news-create :categories="{{ $newsCategories }}"></page-admin-news-create>
@endsection
