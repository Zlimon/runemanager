@extends('layouts.layout')

@section('title')
    Link account
@endsection

@section('content')
    <link href="{{ asset('css/resource-pack.css') }}" rel="stylesheet">
    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                <h1 class="text-center header-chatbox-sword">Link account</h1>

                <div class="d-flex justify-content-center">
                    <div class="col-12 col-md-5">
                        <div class="row mb-3">
                            <label for="username" class="col-5 col-form-label">RuneScape username</label>
                            <div class="col-7">
                                <input type="text" id="username" class="form-control" required autofocus>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="account_type" class="col-5 col-form-label">Account type</label>

                            <div class="col-7" style="margin-top: .5rem;">
                                @foreach (Helper::listAccountTypes() as $accountType)
                                    @if ($accountType === 'normal')
                                        <div class="form-check" style="margin-left: .4rem;">
                                            <input type="radio" id="{{ $accountType }}" name="account_type" class="form-check-input" value="{{ $accountType }}" checked>
                                            <label class="form-check-label" for="{{ $accountType }}">
                                                Normal
                                            </label>
                                        </div>
                                    @else
                                        <div class="icon-radio">
                                            <label class="form-check-label" for="{{ $accountType }}">
                                                <input type="radio" id="{{ $accountType }}" name="account_type" class="form-check-input" value="{{ $accountType }}">
                                                <img src="{{ asset('images/'.$accountType.'.png') }}"
                                                     alt="{{ Helper::formatAccountTypeName($accountType) }} icon"
                                                     title="Click here to select {{ Helper::formatAccountTypeName($accountType) }} as account type for this account">
                                                <span>{{ Helper::formatAccountTypeName($accountType) }}</span>
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="row mb-3">
                            <span class="col-5"></span>
                            <div class="col-7">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
