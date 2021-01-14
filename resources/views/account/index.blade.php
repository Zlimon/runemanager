@extends('layouts.layout')

@section('title')
    Accounts
@endsection

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">

    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        @if ($accounts->isNotEmpty())
            <h1 class="text-center header-chatbox-sword">Search for accounts</h1>

            <div class="s009 mt-4">
                <form method="POST" action="{{ route('account-search') }}">
                    @csrf

                    <div class="inner-form">
                        <div class="basic-search">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <button class="btn btn-dark" type="button" data-toggle="collapse"
                                            data-target="#collapseExample" aria-expanded="false"
                                            aria-controls="collapseExample">
                                        <img src="{{ asset('images/settings.png') }}"
                                             class=""
                                             alt="Settings icon"
                                             title="Click here for advanced search">
                                    </button>
                                </div>
                                <input id="search" type="text"
                                       class="form-control @error('search') is-invalid @enderror"
                                       name="search" value="{{ old('search') }}"
                                       placeholder="{{ $accounts->random()->username }}"
                                       autofocus>
                                <div class="input-group-prepend">
                                    <button class="btn btn-primary rounded-right" type="submit"><i
                                            class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse advance-search background-dialog-iron-rivets" id="collapseExample">
                            <span class="desc">ADVANCED SEARCH</span>
                            <div class="form-row">
                                <div class="col">
                                    <label for="account_type">Account type</label>
                                    <select class="form-control" name="account_type" id="account_type"
                                            data-trigger="">
                                        <option value selected>Any</option>
                                        <option value="normal">Normal</option>
                                        <option value="ironman">Ironman</option>
                                        <option value="hardcore_ironman">Hardcore ironman</option>
                                        <option value="ultimate_ironman">Ultimate ironman</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="order_by">Order by</label>
                                    <select class="form-control" name="order_by" id="order_by"
                                            data-trigger="">
                                        <option value="level" selected>Total level</option>
                                        <option value="xp">XP</option>
                                        <option value="rank">Rank</option>
                                        <option value="account_type">Account type</option>
                                        <option value="user_id">User</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="order_by_order">Order</label>
                                    <select class="form-control" name="order_by_order" id="order_by_order"
                                            data-trigger="">
                                        <option value="asc">Ascending</option>
                                        <option value="desc" selected>Descending</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row second">
                                <div class="col">
                                    <label for="total_level_between_from">Min. total level</label>
                                    <select class="form-control" name="total_level_between_from"
                                            id="total_level_between_from"
                                            data-trigger="">
                                        <option value="0" selected>0</option>
                                        <option value="500">500</option>
                                        <option value="750">750</option>
                                        <option value="1250">1250</option>
                                        <option value="1500">1500</option>
                                        <option value="1750">1750</option>
                                        <option value="2000">2000</option>
                                        <option value="2200">2200</option>
                                        <option value="2277">2277</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="total_level_between_to">Max. total level</label>
                                    <select class="form-control" name="total_level_between_to"
                                            id="total_level_between_to"
                                            data-trigger="">
                                        <option value="500">500</option>
                                        <option value="750">750</option>
                                        <option value="1250">1250</option>
                                        <option value="1500">1500</option>
                                        <option value="1750">1750</option>
                                        <option value="2000">2000</option>
                                        <option value="2200">2200</option>
                                        <option value="2277" selected>2277</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row third">
                                <div class="input-group">
                                    <div class="result-count">
                                        <span>{{ $accounts->count() }} </span>results
                                    </div>
                                    <div class="group-btn">
                                        <button class="btn btn-danger" id="delete">RESET</button>
                                        <button class="btn btn-primary">SEARCH</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if ($query)
                <h1 class="text-center header-chatbox-sword">Search results for "{{ $query }}"</h1>
            @else
                <h1 class="text-center header-chatbox-sword">Accounts</h1>
            @endif

            <div class="d-flex flex-row flex-wrap justify-content-around">
                @foreach($accounts as $account)
                    <a href="{{ route('account-show', $account->username) }}">
                        <div class="btn button-rectangle background-world-map mb-2">
                            <div class="row align-items-center mb-2">
                                <div class="col-4">
                                    <img
                                        src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png"
                                        class="pixel icon"
                                        alt="Profile icon"
                                        title="Click here to visit this account">
                                </div>

                                <div class="col">
                                    <div class="text-left">
                                        <span>
                                            @if ($account->account_type !== "normal")
                                                <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                                     alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                                            @endif
                                            {{ $account->username }}
                                        </span>
                                        <br>
                                        <span class="font-small">
                                            <img class="pixel"
                                                 src="{{ asset('images/skill/overall.png') }}"
                                                 alt="Total level icon">
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
                <img src="{{ asset('images/ignore.png') }}"
                     class="pixel icon"
                     alt="Sad face">
                <h1>There are no linked accounts</h1>
                <h2 class="text-center">Link an account <a href="{{ route('account-create') }}">here</a>!</h2>
            </div>
        @endif
    </div>
@endsection
