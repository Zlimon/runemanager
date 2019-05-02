@extends('layouts.layout')

@section('title')
	| {{ __('title.task') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.task') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.task') }}</h1>

					<div class="task-progress-body">
						<h3 class="text-center" style="width: 20%;">Easy</h3>
						<h3 class="text-center" style="width: 20%;">Medium</h3>
						<h3 class="text-center" style="width: 20%;">Hard</h3>
						<h3 class="text-center" style="width: 20%;">Elite</h3>
					</div>

					<div class="task-progress-body mb-3">
						<div class="progress" style="width: 20%;">
							<div class="progress-bar bg-success" role="progressbar" style="width: {{$easyProgress}}%" aria-valuenow="{{$easyProgress}}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<div class="progress" style="width: 20%;">
							<div class="progress-bar bg-info" role="progressbar" style="width: {{$mediumProgress}}%" aria-valuenow="{{$mediumProgress}}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<div class="progress" style="width: 20%;">
							<div class="progress-bar bg-warning" role="progressbar" style="width: {{$hardProgress}}%" aria-valuenow="{{$hardProgress}}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<div class="progress" style="width: 20%;">
							<div class="progress-bar bg-danger" role="progressbar" style="width: {{$eliteProgress}}%" aria-valuenow="{{$eliteProgress}}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>

					<h2>Current task</h2>

					<div class="task-body">
						@foreach ($currentUserTasks as $task)
							@switch($task->task->difficulty)
								@case('easy')
									<div class="task-box text-center border-success">
									@break
								@case('medium')
									<div class="task-box text-center border-info">
									@break
								@case('hard')
									<div class="task-box text-center border-warning">
									@break
								@case('elite')
									<div class="task-box text-center border-danger">
									@break
								@default
									<div class="task-box text-center">
							@endswitch
								<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $task->task->icon }}.png" width="72" alt="Task '{{ $task->task->task }}' icon">
								<p>{{ $task->task->task }}</p>
								<p><em>{{ Helper::itemData($task->task->icon, 'examine') }}</em></p>
								<a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="{{ Helper::itemData($task->task->icon, 'url') }}">"{{ Helper::itemData($task->task->icon, 'name') }}" Wiki</a>
								<!-- <form method="POST" action="{{ route('task') }}">
									@method('PATCH')
									@csrf

									<button type="submit" class="btn btn-success">Complete task</button>
								</form> -->
							</div>
						@endforeach
					</div>

					<div class="text-center mb-3">
						<form method="POST" action="{{ route('task') }}">
							@csrf

							<button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Generate task</button>
						</form>
					</div>

					<h2>Completed tasks</h2>
					<table>
						<tr>
							<th></th>
							<th>Task</th>
							<th>Reward</th>
							<th>Completed</th>
						</tr>
						@foreach ($completedUserTasks as $task)
							<tr>
							@switch($task->task->difficulty)
								@case('easy')
									<td style="border-left: solid #38c172 5px;">
									@break
								@case('medium')
									<td style="border-left: solid #6cb2eb 5px;">
									@break
								@case('hard')
									<td style="border-left: solid #ffed4a 5px;">
									@break
								@case('elite')
									<td style="border-left: solid #e3342f 5px;">
									@break
								@default
									<td>
							@endswitch
								<img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $task->task->icon }}.png" alt="Task '{{ $task->task->task }}' icon"></td>
								<td>{{ $task->task->task }}</td>
								<td>{{ $task->task->reward }}</td>
								<td>{{ \Carbon\Carbon::parse($task->updated_at)->format('d. M Y H:i') }}</td>
							</tr>
						@endforeach
					</table>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection