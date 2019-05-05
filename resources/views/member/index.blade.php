@extends('layouts.layout')

@section('title')
	{{ __('title.member') }}
@endsection

@section('content')
	<h1>{{ __('title.member') }}</h1>

	<div class="member-body">
		@foreach($members as $member)
			<a href="{{ route('show-member', $member->id) }}">
				<div class="member-box">
					<p>{{ $member->username }}</p>
					@if ($member->user->icon_id != null)
						<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $member->user->icon_id }}.png" width="54" alt="Profile icon">
					@endif
				</div>
			</a>
		@endforeach
	</div>
@endsection