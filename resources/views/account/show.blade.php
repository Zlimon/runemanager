@extends('layouts.layout')

@section('title')
    {{ $account->username }}
@endsection

@section('content')
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        @if ($account->user->private === 0 || (Auth::check() && $account->user->id == Auth::user()->id))
            <accounthiscore account="{{ $account->username }}"></accounthiscore>

            <div class="row">
                <div class="col-md-5">
                    <h3 class="text-center header-chatbox-sword">Recent Events</h3>

                    <accountnotification :account="{{ $account }}"></accountnotification>
                </div>

                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="text-center header-chatbox-sword">Equipment</h3>

                            <equipment :account="{{ $account }}"></equipment>
                        </div>

                        <div class="col-md-6 " style="">
                            <h3 class="text-center header-chatbox-sword">Quests</h3>

                            <div class="background-dialog-iron-rivets p-1 mb-1 pl-2">
                                <quests :account="{{ $account }}" style="max-height: 15rem; overflow: scroll; overflow-x: hidden;"></quests>
                            </div>
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
@endsection
