@extends('layouts.layout')

@section('title')
    Groups
@endsection

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">

    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        @if ($groups->isNotEmpty())
            <h1 class="text-center header-chatbox-sword">Search for groups</h1>

            <div class="s009 mt-4">
                <form method="POST" action="{{ route('group-search') }}">
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
                                       placeholder="{{ $groups->random()->name }}"
                                       autofocus>
                                <div class="input-group-prepend">
                                    <button class="btn btn-primary rounded-right" type="submit"><i
                                            class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse advance-search background-dialog-iron-rivets" id="collapseExample">
                            <h2 class="text-center header-chatbox-sword desc">Advanced search</h2>
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
                                        <span>{{ $groups->count() }}</span> results
                                    </div>
                                    <div class="group-btn">
                                        <button class="btn btn-lg button-combat-style-narrow button-rectangle-small">
                                            <span>Reset</span>
                                        </button>
                                        <button class="btn btn-lg button-combat-style-narrow button-rectangle-small">
                                            <span>Search</span>
                                        </button>
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
                <h1 class="text-center header-chatbox-sword">Groups</h1>
            @endif

            <div class="d-flex flex-row flex-wrap justify-content-around">
                @foreach($groups as $group)
                    <a href="{{ route('group-show', $group->name) }}">
                        <div class="btn button-rectangle background-world-map mb-2">
                            <div class="row align-items-center mb-2">
                                <div class="col-4">
                                    <img
                                        src="custom_group_icon.png"
                                        class="pixel icon"
                                        alt="Profile icon"
                                        title="Click here to visit this group">
                                </div>

                                <div class="col">
                                    <div class="text-left">
                                        <span>
{{--                                            @if ($group->account_type !== "normal")--}}
{{--                                                <img src="{{ asset('images/'.$group->account_type.'.png') }}"--}}
{{--                                                     alt="{{ Helper::formatAccountTypeName($group->account_type) }} icon">--}}
{{--                                            @endif--}}
                                            {{ $group->name }}
                                        </span>
                                        <br>
                                        <span class="font-small">
                                            <img class="pixel"
                                                 src="{{ asset('images/skill/total.png') }}"
                                                 alt="Total level icon">
{{--                                            {{ $group->level }}--}}
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
                <h1>There are no linked groups</h1>
                <h2 class="text-center">Create a group <a href="{{ route('group-create') }}">here</a>!</h2>
            </div>
        @endif
    </div>
@endsection
