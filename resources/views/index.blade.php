@extends('layouts.layout')

@section('title')
    Home
@endsection

@section('content')
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-md-9 mb-3">
            <div class="col-md-12 p-4 align-self-center bg-dark text-light background-dialog-panel">
                <h1>Welcome to {{ config('app.name', 'RuneManager') }}</h1>
                <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="col-md-12 p-4 bg-dark text-light background-dialog-panel">
                @guest
                    <div class="align-self-center">
                        <a href="{{ route('login') }}">
                            <div class="btn btn-block button-combat-style-narrow">
                                <span>Log in</span>
                            </div>
                        </a>

                        <a href="{{ route('register') }}">
                            <div class="btn btn-block button-combat-style-narrow">
                                <span>Register</span>
                            </div>
                        </a>
                    </div>
                @else
                    <a href="{{ route('home') }}">
                        <div class="background-world-map">
                            <img
                                src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Auth::user()->icon_id }}.png"
                                class="pixel icon"
                                alt="Profile icon"
                                title="Click here to visit your profile">
                            <span>{{ Auth::user()->name }}</span>
                        </div>
                    </a>

                    <a href="{{ route('home') }}">
                        <div class="btn btn-block button-combat-style-thin">
                            <span>Profile</span>
                        </div>
                    </a>

                    <a href="{{ route('user-edit') }}">
                        <div class="btn btn-block button-combat-style-thin">
                            <span>Edit profile</span>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-dark text-light background-dialog-panel p-4 mb-3">
        <div class="col-md-12">
            <div class="row justify-content-between">
                <div class="col-md-3">
                    <a href="{{ route('hiscore', ['skill', 'overall']) }}">
                        <div class="btn btn-block button-combat-style-thin">
                            <span>Hiscores</span>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="#">
                        <div class="btn btn-block button-combat-style-thin">
                            <span>Calendar</span>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="{{ route('account') }}">
                        <div class="btn btn-block button-combat-style-thin">
                            <span>Account</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row justify-content-around">
                <div class="hiscore-button-parent">
                    <div class="hiscore-button bg-one">
                        <a href="{{ route('hiscore', ['skill', 'overall']) }}">Skills</a>
                    </div>
                </div>

                <div class="hiscore-button-parent right">
                    <div class="hiscore-button bg-two">
                        <a href="{{ route('hiscore', ['boss', 'abyssal sire']) }}">Bosses</a>
                    </div>
                </div>

                <div class="hiscore-button-parent">
                    <div class="hiscore-button bg-three">
                        <a href="#">Clues</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($recentPosts->isNotEmpty())
        <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
            <h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

            @foreach ($recentPosts as $post)
                <article class="col-md-12 latest-news mt-4 pt-4 background-dialog-iron-rivets">
                    <div class="row">
                        <div class="col-4">
                            <div class="image">
                                <a href="{{ route('news-show', $post->id) }}">
                                    <img
                                        src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}"
                                        class="middle-image"
                                        alt="'{{ $post->title }}' news post image"
                                        title="Click here to read about '{{ $post->title }}'">
                                </a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="title">
                                <span><a href="{{ route('news-show', $post->id) }}">{{ $post->title }}</a></span>
                            </div>
                            <div class="meta">
                                <p>{{ $post->user->name }} | {{ $post->category->category }}</p>
                            </div>
                            <div class="shortstory">
                                <span>{{ $post->shortstory }}</span>
                                <p>
                                    <strong>
                                        <a href="{{ route('news-show', $post->id) }}">
                                            <span>Read more <i class="fas fa-long-arrow-alt-right"></i></span>
                                        </a>
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="date">
                                <span class="month">{{ \Carbon\Carbon::parse($post->created_at)->format('M') }}</span>
                                <br>
                                <span class="day">{{ \Carbon\Carbon::parse($post->created_at)->format('d') }}</span>
                            </div>
                        </div>
                    </div>
                </article>

                @if ($loop->last)
                    <a class="btn button-combat-style-narrow mt-3" href="{{ route('news') }}">
                        <span>Read more <i class="fas fa-long-arrow-alt-right"></i></span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
@endsection
