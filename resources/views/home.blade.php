@extends('layouts.layout')

@section('title')
	{{ Auth::user()->name }}
@endsection

@section('content')
	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
		<div class="row mb-3">
			<div class="col-md-4">
				<div class="background-world-map rounded p-1 text-center">
					<img class="pixel" style="margin-right: -15px;" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="150" alt="Profile icon">
					<br>
					<span><a href="{{ route('user-edit') }}">Edit profile</a></span>
				</div>
			</div>

			<div class="col-md-5">
				<h1 class="text-left">Welcome, {{ Auth::user()->name }}</h1>

				<p>Joined: <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y') }}</strong></p>
			</div>

			<div class="col-md-3">
				@if ($user->private === 0)
					<span>Current status:</span>
					<br>
					<img class="align" src="{{ asset('images') }}/friend.png" alt="Friend icon" title="Currently not private">
					<span><strong>Not private</strong></span>
				@else
					<span>Current status:</span>
					<br>
					<img class="align" src="{{ asset('images') }}/ignore.png" alt="Ignore icon" title="Currently private">
					<span><strong>Private</strong></span>
				@endif
			</div>
		</div>

		<div class="background-dialog-iron-rivets px-4 pt-1">
			<h3 class="text-center">Accounts</h3>

			<hr>

			@foreach ($user->account as $account)
				<div class="row align-items-center">
					<div class="col-md-8">
						<p>@if ($account->account_type != "normal")<img class="pixel mr-1" src="{{ asset('images') }}/{{ $account->account_type }}.png" style="width: 20px;" alt="Account type icon">@endif<strong>{{ $account->username }}</strong></p>
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

		@foreach ($user->account as $account)
			<div class="py-2" style="clear: both;"></div>
			<accounthiscore account="{{ $account->username }}"></accounthiscore>
		@endforeach
	</div>
@endsection