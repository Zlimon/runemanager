@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
@section('navigation')
    <form method="POST" action="{{ route('admin-delete-newspost', $newsPost) }}">
        @method('DELETE')
        @csrf

        <button type="submit" class="btn btn-danger">Delete newspost</button>
    </form>
@endsection

<div class="content-body">
    <div class="text-center pb-3">
        <img src="{{ asset('storage') }}/{{ $newsPost->image->image_file_name }}.{{ $newsPost->image->image_file_extension }}"
             alt="'{{ $newsPost->title }}' news post image" width="50%">
    </div>
    <h1>{{ $newsPost->title }}</h1>
    <p class="text-center"><em>{{ $newsPost->shortstory }}</em></p>
    {!! $newsPost->longstory !!}
</div>
@endsection
