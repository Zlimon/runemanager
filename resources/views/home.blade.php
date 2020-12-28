@extends('layouts.layout')

@section('title')
    {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <div class="row mb-3">
            <div class="col-md-9">
                <img class="pixel float-left" style="margin-right: -15px;"
                     src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="150"
                     alt="Profile icon">
                <h1 class="text-left">Welcome, {{ Auth::user()->name }}</h1>

                <p>Joined: <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y') }}</strong></p>
            </div>

            <div class="col-md-3">
                <span>Current status:</span>
                <br>
                @if ($user->private === 0)
                    <img src="{{ asset('images/friend.png') }}"
                         alt="Happy face"
                         title="Your profile is currently NOT private">
                    <span><strong>Not private</strong></span>
                @else
                    <img src="{{ asset('images/ignore.png') }}"
                         alt="Sad face"
                         title="Your profile is currently private">
                    <span><strong>Private</strong></span>
                @endif

                <p><a href="{{ route('user-edit') }}">Edit profile</a></p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="background-dialog-iron-rivets px-4 pt-1">
                    <h3 class="text-center">Accounts</h3>

                    <hr>

                    @foreach ($user->account as $account)
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <p>
                                    @if ($account->account_type !== "normal")
                                        <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                             class="pixel"
                                             alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon"
                                             style="width: 1rem;">
                                    @endif
                                    <strong>{{ $account->username }}</strong>
                                </p>
                            </div>

                            <div class="col-md-4">
                                <span>Total level:</span>
                                <br>
                                <span><strong>{{ $account->level }}</strong></span>
                            </div>
                        </div>

                        <hr>
                    @endforeach

                    <div class="text-center">
                        <a href="{{ route('account-create') }}">
                            <div class="btn btn-lg button-combat-style-thin">
                                <span>Link account</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($user->account as $account)
            <div class="py-2" style="clear: both;"></div>
            <div class="row">
                <div class="col-md-8">
                    <accounthiscore account="{{ $account->username }}"></accounthiscore>
                </div>

                <div class="col-md-4">
                    <accountnotification :account="{{ $account }}"></accountnotification>
                </div>
            </div>
        @endforeach
    </div>
@endsection
