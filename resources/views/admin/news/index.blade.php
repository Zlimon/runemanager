@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <page-admin-news-index :news-posts="{{ $newsPosts }}"></page-admin-news-index>
@endsection
