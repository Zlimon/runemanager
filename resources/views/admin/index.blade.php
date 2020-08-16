@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	<div style="color: black;">
		<div class="card-deck">
			<div class="card text-center">
				<div class="card-body">
					<div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
					<h5 class="card-title">Total users</h5>
					<p class="card-text"><strong>{{ $users }}</strong></p>
				</div>
			</div>

			<div class="card text-center">
				<div class="card-body">
					<div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
					<h5 class="card-title">Lorem ipsum</h5>
					<p class="card-text"><strong>dolor sit amet</strong></p>
				</div>
			</div>

			<div class="card text-center">
				<div class="card-body">
					<div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
					<h5 class="card-title">Lorem ipsum</h5>
					<p class="card-text"><strong>dolor sit amet</strong></p>
				</div>
			</div>

			<div class="card text-center">
				<div class="card-body">
					<div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
					<h5 class="card-title">Lorem ipsum</h5>
					<p class="card-text"><strong>dolor sit amet</strong></p>
				</div>
			</div>
		</div>

		<div class="card-deck mt-5">
			@for ($i = 0; $i < 2; $i++)
			<div class="card border-0">
				<div class="card-header text-light" style="background: #6d7fcc;">Tittel</div>
				<div class="card-body">
					<h5 class="card-title">Lorem ipsum</h5>
					<p class="card-text"><strong>dolor sit amet</strong></p>
				</div>
			</div>
			@endfor
		</div>

		<div class="card-deck mt-5">
			@for ($i = 0; $i < 2; $i++)
			<div class="card border-0">
				<div class="card-header text-light" style="background: #6d7fcc;">Tittel</div>
				<div class="card-body">
					<h5 class="card-title">Lorem ipsum</h5>
					<p class="card-text"><strong>dolor sit amet</strong></p>
				</div>
			</div>
			@endfor
		</div>
	</div>
@endsection