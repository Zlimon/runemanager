@extends('layouts.layout')

@section('title')
	{{ $account->username }}
@endsection

@section('content')
	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
		@if ($account->user->private === 0 || (Auth::check() && $account->user->id == Auth::user()->id))
			<div class="profile-icon">
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png" width="150" alt="Profile icon">
			</div>

			<accounthiscore account="{{ $account->username }}"></accounthiscore>
		@else
			<div class="text-center py-5">
				<img class="pixel" src="{{ asset('images') }}/ignore.png" width="75px" alt="Sad face">
				<h1>This user is private</h1>
			</div>
		@endif
	</div>
@endsection