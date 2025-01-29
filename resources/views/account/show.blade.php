@extends('layouts.layout')

@section('title')
    {{ $account->username }}
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                @if ($account->user->private === 0 || (Auth::check() && $account->user->id == Auth::user()->id))
                    <div class="d-flex justify-content-between">
                        <div class="col">
                            <h1 class="text-center header-chatbox-sword">{{ $account->username }}</h1>

                            <div class="d-flex justify-content-center">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/3419.png"
                                     class="pixel icon"
                                     alt="Profile icon"
                                     style="width: 7.5rem; height: 7.5rem;">

                                <div class="col">
                                    <span>Rank: <strong>{{ $account->rank }}</strong></span>
                                    <br>
                                    <span>Total XP: <strong>{{ $account->xp }}</strong></span>
                                    <br>
                                    <span>Total Level: <strong>{{ $account->level }}</strong></span>
                                    <br>
                                    <span>Joined: <strong>{{ $account->created_at }}</strong></span>
                                    <br>
                                    <span>Online? <strong>{{ $account->online }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <accounthiscore :account="{{ $account }}"></accounthiscore>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <h3 class="text-center header-chatbox-sword">Recent Notifications</h3>

                            <accountevent :account="{{ $account }}"></accountevent>
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="text-center header-chatbox-sword">Equipment</h3>

                                    <equipment :account="{{ $account }}"></equipment>
                                </div>

                                <div class="col-md-6">
                                    <h3 class="text-center header-chatbox-sword">Quests</h3>

                                    <quests :account="{{ $account }}"></quests>
                                </div>
                            </div>

                            <h3 class="text-center header-chatbox-sword">The Bank of Gielinor</h3>

                            <bank :account="{{ $account }}"></bank>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('images/ignore.png') }}"
                             class="pixel icon"
                             alt="Sad face">
                        <h1>This user is private</h1>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
