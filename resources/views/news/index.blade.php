@extends('layouts.layout')

@section('title')
    News
@endsection

@section('content')
    <div class="container">
        <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
            <h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

            @forelse ($newsPosts as $post)
                @if ($loop->first)
                    <div class="card-columns"> @endif
                        <div class="card text-white bg-dark background-dialog-iron-rivets">
                            <a href="{{ route('news-show', $post->id) }}">
                                <img class="card-img-top"
                                     src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}"
                                     alt="'{{ $post->title }}' news post image">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><a
                                        href="{{ route('news-show', $post->id) }}">{{ $post->title }}</a></h5>
                                <p class="card-text">{{ $post->shortstory }}</p>
                            </div>
                            <div class="card-footer">
                                <small><a href="{{ route('news-show', $post->id) }}">Read more <i
                                            class="fas fa-long-arrow-alt-right"></i></a></small>
                            </div>
                        </div>
                        @if ($loop->last) </div> @endif
            @empty
                <div class="text-center py-5">
                    <img class="pixel" src="{{ asset('images/ignore.png') }}" width="75px" alt="Sad face">
                    <h1>Nothing interesting is happening</h1>
                </div>
            @endforelse
        </div>
    </div>
@endsection
