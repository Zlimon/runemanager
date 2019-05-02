@extends('layouts.layout')

@section('title')
	| {{ __('title.update-log') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.update-log') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.update-log') }}</h1>

					@foreach ($updates as $log)
						<p>{{ $log->username }} <i class="fas fa-long-arrow-alt-right"></i> {{ \Carbon\Carbon::parse($log->updated_at)->format('d. M Y H:i') }}</p>
					@endforeach
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection