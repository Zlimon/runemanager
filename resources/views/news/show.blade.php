@extends('layouts.layout')

@section('title')
    {{ $newsPost->title }}
@endsection

@section('content')
    <link href="{{ asset('css/news.css') }}" rel="stylesheet">

    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                <img src="{{ asset('storage') }}/{{ $newsPost->image->image_file_name }}.{{ $newsPost->image->image_file_extension }}"
                     class="float-end"
                     alt="'{{ $newsPost->title }}' news post image">

                <h1>{{ $newsPost->title }}</h1>
                <p><em>{{ $newsPost->shortstory }}</em></p>

                <hr>

                <p class="float-end">{{ $newsPost->created_at }}</p>

                {!! $newsPost->longstory !!}

                <p class="float-left"><em>- {{ $newsPost->user->name }}</em></p>
            </div>
        </div>
    </div>
@endsection
