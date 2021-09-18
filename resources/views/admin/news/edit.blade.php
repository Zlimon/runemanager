@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    @section('navigation')
        <form method="POST" action="{{ route('admin-delete-newspost', $newsPost) }}" class="p-2">
            @method('DELETE')
            @csrf

            <button type="submit" class="btn btn-danger">Delete newspost</button>
        </form>
    @endsection

    <h1>Update newspost "{{ $newsPost->title }}"</h1>

    <div class="row">
        <div class="col-5">
            <img src="{{ asset('storage') }}/{{ $newsPost->image->image_file_name }}.{{ $newsPost->image->image_file_extension }}"
                 class="w-100"
                 alt="'{{ $newsPost->title }}' news post image">
        </div>

        <div class="col-7 bg-admin-dark p-4">
            <newsupdate :news-post="{{ $newsPost }}" :categories="{{ $newsCategories }}"></newsupdate>
        </div>
    </div>
@endsection
