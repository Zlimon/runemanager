@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <a class="btn btn-primary" href="{{ route('admin-edit-newspost', $newsPost->id) }}">Edit</a>
        </div>

        <div class="p-2">
            <form method="POST" action="{{ route('admin-delete-newspost', $newsPost) }}">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn btn-danger">Delete newspost</button>
            </form>
        </div>
    @endsection

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="p-4 bg-admin-dark">
                <div class="text-center pb-3">
                    <img src="{{ asset('storage') }}/{{ $newsPost->image->image_file_name }}.{{ $newsPost->image->image_file_extension }}"
                         class="w-50"
                         alt="'{{ $newsPost->title }}' news post image">
                </div>
                <h1 class="text-center">{{ $newsPost->title }}</h1>
                <p class="text-center"><em>{{ $newsPost->shortstory }}</em></p>
                {!! $newsPost->longstory !!}
            </div>
        </div>
    </div>
@endsection
