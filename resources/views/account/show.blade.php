@extends('layouts.layout')

@section('title')
	{{ $account->username }}
@endsection

@section('content')
	@if ($account->user->private === 0 || (Auth::check() && $account->user->id == Auth::user()->id))
		@if ($account->user->icon_id != null)
			<div class="profile-icon">
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png" width="150" alt="Profile icon">
			</div>
		@elseif ((Auth::check() && $account->user->id == Auth::user()->id) && $account->user->icon_id == null)
			<div class="profile-icon">
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Helper::randomItemId() }}.png" width="150" alt="Profile icon">
				<p>Get your own profile icon <a href="{{ route('edit-user') }}">here</a>!</p>
			</div>
		@endif

		<account :account-id="{{ $account->id }}"></account>
	@else
		<div class="text-center py-5">
			<img class="pixel" src="{{ asset('images') }}/ignore.png" width="75px" alt="Sad face">
			<h1>This user is private</h1>
		</div>
	@endif
@endsection