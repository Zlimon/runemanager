@extends('layouts.layout')

@section('title')
    News
@endsection

@section('content')
    <link href="{{ asset('css/news.css') }}" rel="stylesheet">

    <div class="bg-dark background-dialog-panel p-3">
        @if ($newsPosts->isNotEmpty())
            <h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

            <div class="row row-cols-1 row-cols-md-4 g-4">
                @foreach ($newsPosts as $post)
                    <div class="col">
                        <div class="card bg-dark background-dialog-panel">
                            <div class="category">{{ $post->newsCategory->category }}</div>
                            <img src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}"
                                 class="card-img-top"
                                 alt="'{{ $post->title }}' news post image"
                                 style="max-height: 15rem; object-fit: cover;">

                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">{{ $post->title }}</h5>
                                        <p class="card-text"><em>{{ $post->shortstory }}</em></p>
                                    </div>

                                    <div class="date text-center">
                                        <span class="month">{{ \Carbon\Carbon::parse($post->created_at)->format('M') }}</span>
                                        <br>
                                        <span class="day">{{ \Carbon\Carbon::parse($post->created_at)->format('d') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted text-end">
                                <a class="btn button-combat-style-narrow" href="{{ route('news-show', $post->id) }}">
                                    Read more <i class="fas fa-long-arrow-alt-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <img src="{{ asset('images/ignore.png') }}"
                     class="pixel icon"
                     alt="Sad face">
                <h1>Nothing interesting is happening</h1>
            </div>
        @endif
    </div>
@endsection
