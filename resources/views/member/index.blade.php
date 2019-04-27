@extends('layouts.layout')

@section('title')
	| {{ __('title.member') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.member') }}</span>
					<span class="float-right">Next update: </span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.member') }}</h1>

					<div class="member-body">
						@foreach($members as $member)
							<div class="member-box">
								<a href="{{ route('show-member', $member->username) }}">
									<span>{{ $member->username }}</span>
								</a>
								<br>
								<span class="small">Title</span>
								<br>
								<img src="https://www.osrsbox.com/osrsbox-db/items-icons/12453.png">
							</div>
						@endforeach
					</div>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection