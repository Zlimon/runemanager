@extends('layouts.layout')

@section('title')
	{{ __('title.create-newspost') }}
@endsection

@section('content')
	<h1>{{ __('title.create-newspost') }}</h1>

	<form method="POST" action="{{ route('create-newspost') }}">
		@csrf

		<div class="form-group row">
			<label for="title" class="col-md-4 col-form-label text-md-right">Title</label>

			<div class="col-md-6">
				<input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autofocus>

				@error('title')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>
		</div>

		<div class="form-group row">
			<label for="category_id" class="col-md-4 col-form-label text-md-right">Category</label>

			<div class="col-md-6">
				<select id="category_id" class="form-control" name="category_id">
					@foreach ($categories as $category)
						<option value="{{ $category->id }}">{{ $category->category }}</option>
					@endforeach
				</select>

				@error('category_id')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>
		</div>

		<div class="form-group row">
			<label for="shortstory" class="col-md-4 col-form-label text-md-right">Shortstory</label>

			<div class="col-md-6">
				<input id="shortstory" type="text" class="form-control @error('shortstory') is-invalid @enderror" name="shortstory" value="{{ old('shortstory') }}" required autofocus>

				@error('shortstory')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>
		</div>

		<textarea class="form-control" id="longstory" name="longstory"></textarea>

		@error('longstory')
			<span class="invalid-feedback" role="alert">
				<strong>{{ $message }}</strong>
			</span>
		@enderror

		<div class="form-group row mb-0">
			<button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Post news</button>
		</div>
	</form>

	<script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
	<script>
		CKEDITOR.replace( 'longstory' );
	</script>
@endsection