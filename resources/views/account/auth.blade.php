@extends('layouts.layout')

@section('title')
	{{ __('title.create-member') }}
@endsection

@section('content')
	<h1>Status pending...</h1>

	<p>Code: {{ $authStatus->code }}</p>

	<form method="POST" action="{{ route('create-account') }}">
		@csrf

		<div class="form-group row mb-0">
			<div class="col-md-8 offset-md-4">
				<button type="submit" class="btn btn-primary">Link</button>
			</div>
		</div>
	</form>
@endsection