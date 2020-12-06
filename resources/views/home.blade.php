@extends('layouts.layout')

@section('title')
	{{ Auth::user()->name }}
@endsection

@section('content')
	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3">
		<div class="row">
			<div class="col-md-4">
				<div class="profile-icon rounded p-1 text-center">
					@if ($user->icon_id)
						<img class="pixel" style="margin-right: -15px;" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="150" alt="Profile icon">
						<br>
						<span><a href="{{ route('user-edit') }}">Edit profile</a></span>
					@else
						<img class="pixel" style="margin-right: -15px;" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Helper::randomItemId() }}.png" width="150" alt="Profile icon">
						<br>
						<span>Get your own profile icon <a href="{{ route('user-edit') }}">here</a>!</span>
					@endif
				</div>
			</div>

			<div class="col-md-5">
				<h1 class="text-left">Welcome, {{ Auth::user()->name }}</h1>

				<p>Joined: <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y') }}</strong></p>

				<p>Accounts:</p>

				<div class="row align-items-center">
					<div class="col-md-6">
						@foreach ($user->account as $account)
							<p><strong>{{ $account->username }}</strong></p>
						@endforeach
					</div>

					<div class="col-md-6">
						<a href="{{ route('account-create') }}" class="btn rs-button">Link account</a>
					</div>
				</div>
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

		@foreach ($user->account as $account)
			<div class="py-2" style="clear: both;"></div>
			<accounthiscore account="{{ $account->username }}"></accounthiscore>
		@endforeach
	</div>
@endsection