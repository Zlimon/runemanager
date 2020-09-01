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

		<div class="form-group row">
			<label for="mode" class="col-md-4 col-form-label text-md-right">Mode</label>

			<div class="col-md-6">
				<div class="form-check" style="margin-left: .45rem;">
					<label class="form-check-label" for="normal">
						<input id="normal" type="radio" class="form-check-input @error('mode') is-invalid @enderror" name="mode" value="normal" checked>
						Normal
					</label>
				</div>

				<div class="icon-radio">
					<label class="icon-radio form-check-label" for="ironman">
						<input id="ironman" type="radio" class="form-check-input @error('mode') is-invalid @enderror" name="mode" value="ironman">
						<img class="align" src="{{ asset('images') }}/ironman.png" alt="Ironman icon" title="Click here to select Ironman mode for your account">
						Ironman
					</label>
				</div>

				<div class="icon-radio">
					<label class="icon-radio form-check-label" for="hardcore">
						<input id="hardcore" type="radio" class="form-check-input @error('mode') is-invalid @enderror" name="mode" value="hardcore">
						<img class="align" src="{{ asset('images') }}/hardcore.png" alt="Hardcore ironman icon" title="Click here to select Harcore Ironman mode for your account">
						Hardcore Ironman
					</label>
				</div>

				<div class="icon-radio">
					<label class="icon-radio form-check-label" for="ultimate">
						<input id="ultimate" type="radio" class="form-check-input @error('mode') is-invalid @enderror" name="mode" value="ultimate">
						<img class="align" src="{{ asset('images') }}/ultimate.png" alt="Ultimate ironman icon" title="Click here to select Ultimate Ironman mode for your account">
						Ultimate Ironman
					</label>
				</div>

				<div class="icon-radio">
					<label class="icon-radio form-check-label" for="group">
						<input id="group" type="radio" class="form-check-input @error('mode') is-invalid @enderror" name="mode" value="group" disabled>
						<img class="align" src="{{ asset('images') }}/group.png" alt="Ultimate ironman icon" title="Click here to select Group Ironman mode for your account">
						Group Ironman (TBA)
					</label>
				</div>

				@error('mode')
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