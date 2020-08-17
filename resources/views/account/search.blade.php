@extends('layouts.layout')

@section('title')
	{{ __('title.search-account') }} "{{ $query }}"
@endsection

@section('content')
	<h1>Search for accounts</h1>

	<form class="form-group row" method="POST" action="{{ route('search-account') }}">
		@csrf

		<div class="col-md-3"></div>

		<div class="col-md-6 mb-2">
			<input id="search" type="text" class="form-control @error('search') is-invalid @enderror" name="search" value="{{ old('search') }}" placeholder="{{ $searchResults->random()->username }}" autofocus>
			
			@error('search')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<button type="submit" class="btn btn-primary mb-2"><i class="fas fa-search"></i></button>
	</form>

	@if (count($searchResults) >= 1)
		<h1>Search results for "{{ $query }}"</h1>

		<div class="account-body">
			@foreach($searchResults as $result)
				<a href="{{ route('show-account', $result->id) }}">
					<div class="account-box">
						<p>{{ $result->username }}</p>
						@if ($result->user->icon_id)
							<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $result->user->icon_id }}.png" width="54" alt="Profile icon">
						@endif
					</div>
				</a>
			@endforeach
		</div>
	@endif
@endsection