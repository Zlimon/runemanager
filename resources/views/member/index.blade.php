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
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.member') }}</h1>

					<div class="member-body">
						@foreach($members as $member)
							<a href="{{ route('show-member', $member->id) }}">
								<div class="member-box">
									<p>{{ $member->username }}</p>
									@if ($member->user->icon_id != null)
										<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $member->user->icon_id }}.png" width="54" alt="Profile icon">
									@endif
								</div>
							</a>
						@endforeach
					</div>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection