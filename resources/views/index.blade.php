@extends('layouts.layout')

@section('title')
    Home
@endsection

@section('content')
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">

    <div class="row">
        <div class="jumbotron d-flex flex-column flex-md-row justify-content-center justify-content-md-between align-items-center mb-3"
             id="jumbotron">
            <div class="m-5">
                <p class="display-1" style="font-family: 'runescape-smooth', sans-serif;">
                    Welcome to
                    <br>
                    {{ config('app.name', 'RuneManager') }}
                </p>

                <div class="d-flex">
                    <a href="{{ route('login') }}" class="btn btn-block button-combat-style-thin">
                        Log in
                    </a>

                    <a href="{{ route('register') }}" class="btn btn-block button-combat-style-thin">
                        Register
                    </a>
                </div>
            </div>

            <div class="d-none d-md-block mx-4" style="margin-top: -4rem; width: 25rem; height: 27rem;">
                <div class="bg-dark background-dialog-panel p-3">
                    <ul class="nav nav-tabs list-inline mx-auto justify-content-center" id="firstTab" role="tablist" style="border: none;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="discord-tab" data-bs-toggle="tab" data-bs-target="#discord" type="button" role="tab" aria-controls="discord" aria-selected="true">Discord</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab" aria-controls="members" aria-selected="false">Members</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="firstTabContent">
                        <div class="tab-pane fade show active" id="discord" role="tabpanel" aria-labelledby="discord-tab">
                            <div class="h-100 w-100">
                                <fieldset>
                                    <iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark"
                                            title="Preview of RuneManager">
                                    </iframe>
                                </fieldset>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col mb-3">
            <div class="row">
                <div class="d-flex align-items-center">
                    <div class="col-6" style="background: url('{{ asset('images/scenery_1.png') }}'); background-size: cover; background-position: center; height: 15rem;">
                        <span></span>
                    </div>
                    <div class="col px-3">
                        <h3 class="text-center header-chatbox-sword">About</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="d-flex align-items-center">
                    <div class="col px-3">
                        <h3 class="text-center header-chatbox-sword">Features</h3>
                        <p class="text-end">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                    </div>
                    <div class="col-6" style="background: url('{{ asset('images/scenery_4.png') }}'); background-size: cover; background-position: center; height: 15rem;">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="bg-dark background-dialog-panel p-3">
                <ul class="nav nav-tabs list-inline mx-auto justify-content-center" id="secondTab" role="tablist" style="border: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab" aria-controls="calendar" aria-selected="true">Calendar</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">Notifications</button>
                    </li>
                </ul>
                <div class="tab-content" id="secondTabContent">
                    <div class="tab-pane fade show active" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
                        <calendar></calendar>
                    </div>
                    <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                        <h2>Notifications</h2>
                        <announcementall></announcementall>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <div class="bg-dark background-dialog-panel p-3">
                @if ($recentPosts->isNotEmpty())
                    <h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

                    @foreach ($recentPosts as $post)
                        <article class="row">
                            <div class="col-4 d-none d-md-block">
                                <a href="{{ route('news-show', $post->id) }}">
                                    <img src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}"
                                         class="img-fluid w-100"
                                         alt="'{{ $post->title }}' news post image"
                                         title="Click here to read about '{{ $post->title }}'">
                                </a>
                            </div>
                            <div class="col-12 col-md-8 d-flex justify-content-between">
                                <div class="col">
                                    <h5 style="margin-bottom: 0;">
                                        <a href="{{ route('news-show', $post->id) }}">{{ $post->title }}</a>
                                    </h5>
                                    <p>{{ $post->user->name }} | {{ $post->newsCategory->category }}</p>
                                    <p>{{ $post->shortstory }}</p>
                                    <p>
                                        <strong>
                                            <a href="{{ route('news-show', $post->id) }}">
                                                Read more <i class="fas fa-long-arrow-alt-right"></i>
                                            </a>
                                        </strong>
                                    </p>
                                </div>
                                <div class="col-2">
                                    <div class="date text-center">
                                        <span class="month">{{ \Carbon\Carbon::parse($post->created_at)->format('M') }}</span>
                                        <br>
                                        <span class="day">{{ \Carbon\Carbon::parse($post->created_at)->format('d') }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <hr>

                        @if ($loop->last)
                            <div class="text-end">
                                <a class="btn button-combat-style-narrow" href="{{ route('news') }}">
                                    Read more <i class="fas fa-long-arrow-alt-right"></i>
                                </a>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <div class="col d-none d-md-block">
            <div class="card bg-dark background-dialog-panel">
                <div id="category">{{ $recentPosts[0]->newsCategory->category }}</div>
                <img src="{{ asset('storage') }}/{{ $recentPosts[0]->image->image_file_name }}.{{ $recentPosts[0]->image->image_file_extension }}"
                     class="card-img-top"
                     alt="'{{ $recentPosts[0]->title }}' news post image"
                     style="max-height: 15rem; object-fit: cover;">

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">{{ $recentPosts[0]->title }}</h5>
                            <p class="card-text"><em>{{ $recentPosts[0]->shortstory }}</em></p>
                        </div>

                        <div class="date text-center">
                            <span class="month">{{ \Carbon\Carbon::parse($recentPosts[0]->created_at)->format('M') }}</span>
                            <br>
                            <span class="day">{{ \Carbon\Carbon::parse($recentPosts[0]->created_at)->format('d') }}</span>
                        </div>
                    </div>
                    <div class="text-break">{{ $recentPosts[0]->longstory }}</div>
                </div>
                <div class="card-footer text-muted text-end">
                    <a class="btn button-combat-style-narrow" href="{{ route('news') }}">
                        Read more <i class="fas fa-long-arrow-alt-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

<style scoped>
    fieldset {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }

    iframe {
        width: 100%;
        height: 100%;
    }
</style>
