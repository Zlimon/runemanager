@extends('layouts.layout')

@section('title')
	{{ __('title.create-member') }}
@endsection

@section('content')
	<h2>Account authentication status for:</h2>

	<h1>{{ $authStatus->username }}</h1>

	<div class="form-group row">
		<label for="status" class="col-md-4 text-md-right">Status:</label>

		<div class="col-md-6">
			<strong>{{ ucfirst($authStatus->status) }}</strong>
		</div>
	</div>

	<div class="form-group row">
		<label for="code" class="col-md-4 text-md-right">Code:</label>

		<div class="col-md-6">
			<span style="font-family: monospace; font-size: 1.25rem;">{{ $authStatus->code }}</span>
		</div>
	</div>

	<form method="POST" action="{{ route('update-account-auth') }}">
		@csrf
		@method('PATCH')

		<div class="form-group row">
			<label for="account_type" class="col-md-4 text-md-right">Account type:</label>

			<div class="col-md-8">
				<span><img class="align" src="{{ asset('images') }}/{{ $authStatus->account_type }}.png" alt="{{ Helper::formatAccountTypeName($authStatus->account_type) }} icon" title="You have currently picked {{ Helper::formatAccountTypeName($authStatus->account_type) }} as account type for your account"> {{ Helper::formatAccountTypeName($authStatus->account_type) }}</span>

				<div class="row mt-2">
					<div class="col-md-5">
						<select id="account_type" class="form-control" name="account_type">
							@foreach (Helper::listAccountTypes() as $accountType)
								<option value="{{ $accountType }}">{{ Helper::formatAccountTypeName($accountType) }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-4">
						<button type="submit" class="btn btn-primary">Update</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<form method="POST" action="{{ route('delete-account-auth') }}">
		@csrf
		@method('DELETE')

		<div class="form-group row mb-0">
			<div class="col-md-8 offset-md-4">
				<button type="submit" class="btn btn-danger">Delete</button>
			</div>
		</div>
	</form>

	<h2 class="mt-2">How to authenticate:</h2>

	<ol>
		<li>Make sure you have the RuneManager plugin enabled</li>
		<li>Enter your RuneManager user credentials in the RuneManager plugin configurations</li>
		<li>Log in on your Old School RuneScape account</li>
		<li>Type in chat:<br>
			<span style="font-family: monospace; font-size: 1.25rem;">!auth {{ $authStatus->code }}</span>
		</li>
		<li>You should get the response:<br>
			<strong>Attempting to authenticate account {{ $authStatus->username }} to user {{ $authStatus->user->name }}</strong>
		</li>
		<li>And then:<br>
			<strong>Account successfully authenticated!</strong>
		</li>
	</ol>

	<h2>Other responses mean:</h2>

	<ul>
		<li>
			<em>Not a supported account type. Valid account types: normal, ironman, hardcore ironman, ultimate ironman</em><br>
			<span>This means you are attempting to authenticate the account on an unsupported game mode such as DMM, Leagues etc.</span><br>
			<span>To fix this you have to log in to a normal world</span>
		</li>
		<li>
			<em>This account has no pending status</em><br>
			<span>This means this account has (or has not successfully) already been authenticated to RuneManager</span>
		</li>
		<li>
			<em>This account is registered as &lt;account type&gt;, not &lt;account type&gt;</em><br>
			<span>This means you are attempting to authenticate the account with a different account type than the registered account type</span><br>
			<span>To fix this you can update the account type above</span>
		</li>
		<li>
			<em>Invalid code</em><br>
			<span>This means you have most likely written the code wrong. Try again</span>
		</li>
		<li>
			<em>Could not fetch player data from hiscores</em><br>
			<span>This means RuneManager was not able to fetch player hiscores from Old School RuneScape. This is most likely an error on the Old School hiscore server</span>
		</li>
	</ul>
@endsection