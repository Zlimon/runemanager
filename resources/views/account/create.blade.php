@extends('layouts.layout')

@section('title')
    Link account
@endsection

@section('content')
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <h1 class="text-center header-chatbox-sword">Link account</h1>

        <form method="POST" action="{{ route('account-auth-create') }}">
            @csrf

            <div class="form-group row">
                <label for="username" class="col-md-4 col-form-label text-md-right">RuneScape username</label>

                <div class="col-md-6">
                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                           name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                    @error('username')
                    <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="account_type" class="col-md-4 col-form-label text-md-right">Account type</label>

                <div class="col-md-6" style="margin-top: .5rem;">
                    @foreach (Helper::listAccountTypes() as $accountType)
                        @if ($accountType === 'normal')
                            <div class="form-check" style="margin-left: .45rem;">
                                <label class="form-check-label" for="normal">
                                    <input id="{{ $accountType }}" type="radio"
                                           class="form-check-input @error('account_type') is-invalid @enderror"
                                           name="account_type" value="{{ $accountType }}" checked>
                                    Normal
                                </label>
                            </div>
                        @else
                            <div class="icon-radio">
                                <label class="icon-radio form-check-label" for="{{ $accountType }}">
                                    <input id="{{ $accountType }}" type="radio"
                                           class="form-check-input @error('account_type') is-invalid @enderror"
                                           name="account_type" value="{{ $accountType }}">
                                    <img src="{{ asset('images/'.$accountType.'.png') }}"
                                         alt="{{ Helper::formatAccountTypeName($accountType) }} icon"
                                         title="Click here to select {{ Helper::formatAccountTypeName($accountType) }} account type for your account">
                                    {{ Helper::formatAccountTypeName($accountType) }}
                                </label>
                            </div>
                        @endif
                    @endforeach

                    @error('accountType')
                    <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">Link</button>
                </div>
            </div>
        </form>
    </div>
@endsection
