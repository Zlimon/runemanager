@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	@section('navigation')
		<form method="POST" action="{{ route('admin-delete-newspost', $post->id) }}">
			@method('DELETE')
			@csrf

			<button type="submit" class="btn btn-danger">Delete newspost</button>
		</form>
	@endsection

	<h1>Update newspost "{{ $post->title }}"</h1>

	<div class="row">
		<div class="col-5">
			<img src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image" width="100%">
		</div>

		<div class="col-7">
			<form method="POST" action="{{ route('admin-edit-newspost', $post->id) }}" enctype="multipart/form-data">
				@method('PATCH')
				@csrf

				<div class="form-group row">
					<label for="user_id" class="col-md-1 col-form-label text-md-left">Author</label>

					<div class="col-md-6">
						<input id="user_id" type="text" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ $post->user->name }}">

						@error('user_id')
							<span class="invalid-feedback" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
					</div>
				</div>

				<div class="form-group row">
					<label for="image" class="col-md-1 col-form-label text-md-left">Image</label>

					<div class="row">
						<div class="col-md-6">
							<input id="image" type="file" class="form-control-file border rounded bg-white p-1 ml-3 @error('image') border-danger @enderror" name="image" aria-describedby="imageTip" style="color: black;">
							<small id="imageTip" class="form-text ml-3">Current image ID: {{ $post->image_id }}</small>
						</div>

						<div class="col-md-6">
							<input id="default" type="checkbox" class="form-control-input border rounded bg-white p-1 @error('default') border-danger @enderror" name="default">

							<label for="default" class="col-form-label text-md-left">Use default image</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<label for="title" class="col-md-1 col-form-label text-md-left">Title</label>

					<div class="col-md-6">
						<input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $post->title }}">

						@error('title')
							<span class="invalid-feedback" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
					</div>
				</div>

				<div class="form-group row">
					<label for="category_id" class="col-md-1 col-form-label text-md-left">Category</label>

					<div class="col-md-6">
						<select id="category_id" class="form-control" name="category_id">
							<option value="{{ $post->category_id }}">Currently: {{ $post->category->category }}</option>
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
					<label for="shortstory" class="col-md-1 col-form-label text-md-left">Shortstory</label>

					<div class="col-md-11">
						<input id="shortstory" type="text" class="form-control @error('shortstory') is-invalid @enderror" name="shortstory" value="{{ $post->shortstory }}">

						@error('shortstory')
							<span class="invalid-feedback" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
					</div>
				</div>

				<textarea class="form-control" id="longstory" name="longstory">{{ $post->longstory }}</textarea>

				@error('longstory')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror

				<div class="form-group row mb-0">
					<button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Update news</button>
				</div>
			</form>
		</div>

		<script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
		<script>
			CKEDITOR.replace( 'longstory' );
		</script>
	</div>
@endsection