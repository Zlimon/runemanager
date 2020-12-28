@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
@section('navigation')
    <button type="button" class="btn btn-danger">
        <span>Ban user</span>
    </button>
@endsection

<div class="content-body">
    <div class="text-center">
        @if ($user->icon_id)<img class="pixel"
                                 src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png"
                                 width="175" alt="Profile icon">@endif
        <h1>{{ $user->name }}</h1>
        <h3>@role('admin') [Admin] @endrole</h3>
    </div>

    <form method="POST" action="{{ route('admin-edit-user', $user->id) }}">
        @method('PATCH')
        @csrf

        <div class="form-group row">
            <label for="name" class="col-md-4 col-form-label text-md-right">Profile name</label>

            <div class="col-md-4">
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                       value="{{ $user->name }}" autofocus>

                @error('name')
                <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">Email</label>

            <div class="col-md-4">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                       value="{{ $user->email }}">

                @error('email')
                <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="private" class="col-md-4 col-form-label text-md-right">Private</label>

            <div class="col-md-3">
                <select class="form-control" id="private" name="private">
                    <option value="{{ $user->private }}">
                        Currently: {{ ($user->private === 0 ? 'False' : 'True') }}</option>
                    <option value="1">True</option>
                    <option value="0">False</option>
                </select>

                @error('private')
                <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="icon_id" class="col-md-4 col-form-label text-md-right">Profile icon ID</label>

            <div class="col-md-4">
                <input id="icon_id" type="text" class="form-control @error('icon_id') is-invalid @enderror"
                       name="icon_id" value="{{ $user->icon_id }}" aria-describedby="icon_idTip">
                <small id="icon_idTip" class="form-text">Type in the ID of an icon you wish to display as the profile
                    icon. Search icons <a target="_blank" rel="noopener noreferrer"
                                          href="https://www.osrsbox.com/tools/item-search/">here</a></small>

                @error('email')
                <span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
                @enderror
            </div>
        </div>

        @if (count($user->member) > 0)
            <h5 class="text-center">Transfer ownership of OSRS accounts:</h5>

            @foreach ($user->member as $accountNumber => $account)
                <div class="form-group row">
                    <label for="account[{{ $account->id }}]"
                           class="col-md-4 col-form-label text-md-right">{{ $account->username }}</label>

                    <div class="col-md-4">
                        <input id="account[{{ $account->id }}]" type="hidden" name="accountId[{{ $account->id }}]"
                               value="{{ $account->id }}">

                        <input id="account[{{ $account->id }}]" type="text"
                               class="form-control @error('account[{{ $account->id }}]') is-invalid @enderror"
                               name="account[{{ $account->id }}]" placeholder="Username or ID of new owner">

                        @error('account[{{ $account->id }}]')
                        <span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
                        @enderror
                    </div>
                </div>

                @php $accountNumber++ @endphp
            @endforeach
        @endif

        <div class="text-center">
            <button type="submit" class="btn btn-lg btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection
