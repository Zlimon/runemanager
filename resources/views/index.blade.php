@extends('layouts.layout')

@section('title')
	| {{ __('title.index') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-body">
					<h1>{{ __('title.index') }}</h1>

					@for ($i = 0; $i < 50; $i++)
						<p>hei</p>
					@endfor
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection