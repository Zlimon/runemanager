@extends('layouts.layout')

@section('title')
    Accounts
@endsection

@section('content')
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        @if (count($accounts) > 0)
            <h1 class="text-center header-chatbox-sword">Search for accounts</h1>

            <form class="form-group row" method="POST" action="{{ route('account-search') }}">
                @csrf

                <div class="col-md-3"></div>

                <div class="col-md-6 mb-2">
                    <input id="search" type="text" class="form-control @error('search') is-invalid @enderror"
                           name="search" value="{{ old('search') }}" placeholder="{{ $accounts->random()->username }}"
                           autofocus>

                    @error('search')
                    <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-search"></i></button>
            </form>

            @if ($query)
                <h1 class="text-center header-chatbox-sword">Search results for "{{ $query }}"</h1>
            @else
                <h1 class="text-center header-chatbox-sword">Accounts</h1>
            @endif

            <div class="d-flex flex-row flex-wrap justify-content-around">
                @foreach($accounts as $account)
                    <a href="{{ route('account-show', $account->username) }}">
                        <div class="btn button-static background-world-map">
                            <div class="row align-items-center ">
                                <div class="col-4">
                                    <img class="pixel icon"
                                         src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png"
                                         alt="Profile icon">
                                </div>

                                <div class="col">
                                    <div class="text-left">
                                        <span>{{ $account->username }}</span>
                                        <br>
                                        <span class="font-small">
                                            <img class="pixel"
                                                 src="{{ asset('images/skill/overall.png') }}"
                                                 alt="skill icon">
                                            {{ $account->level }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <img class="pixel" src="{{ asset('images/ignore.png') }}" style="width: 75px;" alt="Sad face">
                <h1>There are no linked accounts...</h1>
                <h2 class="text-center">Link an account <a href="{{ route('account-create') }}">here</a>!</h2>
            </div>
        @endif
    </div>
@endsection
