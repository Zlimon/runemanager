@extends('layouts.layout')

@section('title')
	{{ __('title.create-member') }}
@endsection

@section('content')
	<h1>{{ __('title.create-member') }}</h1>

	<form method="POST" action="{{ route('create-account') }}">
		@csrf

		<div class="form-group row">
			<label for="username" class="col-md-4 col-form-label text-md-right">RuneScape username</label>

			<div class="col-md-6">
				<input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

				@error('username')
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
@endsection