@extends('layouts.layout')

@section('title')
    Groups
@endsection

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">

    <div class="bg-dark background-dialog-panel p-3">
        @if ($groups->isNotEmpty())
            <h1 class="text-center header-chatbox-sword">Search for groups</h1>

            <div class="s009 mt-4">
                <form method="POST" action="{{ route('group-search') }}">
                    @csrf

                    <div class="inner-form">
                        <div class="basic-search">
                            <div class="input-group mb-2">
                                <a class="btn btn-dark" data-bs-toggle="collapse" href="#advancedSearch" role="button" aria-expanded="true" aria-controls="advancedSearch">
                                    <img src="{{ asset('images/settings.png') }}"
                                         class="pixel"
                                         alt="Profile icon"
                                         title="Click here for advanced search">
                                </a>
                                <input id="search" type="text"
                                       class="form-control @error('search') is-invalid @enderror"
                                       name="search" value="{{ old('search') }}"
                                       placeholder="{{ $groups->random()->name }}"
                                       autofocus>
                                <div class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>
                        <div class="collapse advance-search background-dialog-iron-rivets" id="advancedSearch">
                            <h2 class="text-center header-chatbox-sword desc">Advanced search</h2>

                            <div class="row mb-3">
                                <div class="col">
                                    <label for="account_type">Account type</label>
                                    <select id="account_type" name="account_type" class="form-control">
                                        <option value selected>Any</option>
                                        <option value="normal">Normal</option>
                                        <option value="ironman">Ironman</option>
                                        <option value="hardcore_ironman">Hardcore ironman</option>
                                        <option value="ultimate_ironman">Ultimate ironman</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="order_by">Order by</label>
                                    <select class="form-control" name="order_by" id="order_by">
                                        <option value="level" selected>Total level</option>
                                        <option value="xp">XP</option>
                                        <option value="rank">Rank</option>
                                        <option value="account_type">Account type</option>
                                        <option value="user_id">User</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="order_by_order">Order</label>
                                    <select class="form-control" name="order_by_order" id="order_by_order">
                                        <option value="asc">Ascending</option>
                                        <option value="desc" selected>Descending</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col">
                                    <label for="total_level_between_from">Min. total level</label>
                                    <select id="total_level_between_from" name="total_level_between_from" class="form-control">
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
                                    <select id="total_level_between_to" name="total_level_between_to" class="form-control">
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
                                    <div>
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
                                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/12810.png"
                                         class="pixel icon"
                                         alt="Group icon"
                                         title="Click here to visit this group">
                                </div>

                                <div class="col">
                                    <div class="text-left">
                                        <span>
                                            {{ $group->name }}
                                        </span>
                                        <br>
                                        <span class="font-small">
                                            <img src="{{ asset('images/skill/total.png') }}"
                                                 class="pixel"
                                                 alt="Total level icon">
                                            484
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
