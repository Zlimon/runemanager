@extends('layouts.layout')

@section('title')
	| {{ __('title.create-member') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('home') }}">{{ __('title.home') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.create-member') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.create-member') }}</h1>

					<form method="POST" action="{{ route('create-member') }}">
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
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection