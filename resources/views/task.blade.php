@extends('layouts.layout')

@section('title')
	{{ __('title.task') }}
@endsection

@section('content')
	<h1>{{ __('title.task') }}</h1>

	<div class="task-progress-body">
		<h3 class="text-center" style="width: 20%;">Easy ({{ $easyProgress }}%)</h3>
		<h3 class="text-center" style="width: 20%;">Medium ({{ $mediumProgress }}%)</h3>
		<h3 class="text-center" style="width: 20%;">Hard ({{ $hardProgress }}%)</h3>
		<h3 class="text-center" style="width: 20%;">Elite ({{ $eliteProgress }}%)</h3>
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

	<h2>Current tasks</h2>

	@if (count($currentAccountTasks) > 0)
		<div class="card-deck">
			@foreach ($currentAccountTasks as $task)
				@switch($task->task->difficulty)
					@case('easy')
						<div class="card task-box text-center border-success">
						@break
					@case('medium')
						<div class="card task-box text-center border-info">
						@break
					@case('hard')
						<div class="card task-box text-center border-warning">
						@break
					@case('elite')
						<div class="card task-box text-center border-danger">
						@break
					@default
						<div class="card task-box text-center">
				@endswitch
					<div class="card-body">
						<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $task->task->icon }}.png" width="72" alt="Task '{{ $task->task->task }}' icon">
						<h5 class="card-title">{{ $task->task->task }}</h5>
						<p class="card-text"><em>{{ Helper::itemData($task->task->icon, 'examine') }}</em></p>

						<form method="POST" action="{{ route('task') }}">
							@method('PATCH')
							@csrf

							<input id="task_id" type="hidden" name="task_id" value="{{ $task->task_id }}" required>

							<button type="submit" class="btn btn-success">Complete task</button>
						</form>
					</div>
					<div class="card-footer">
						<a target="_blank" rel="noopener noreferrer" href="{{ Helper::itemData($task->task->icon, 'url') }}">"{{ Helper::itemData($task->task->icon, 'name') }}" Wiki</a>
					</div>
				</div>
			@endforeach
		</div>
	@else
		<div class="text-center">
			<h3 class="text-danger text-center">Currently no tasks to do!</h3>
			<img class="pixel" src="{{ asset('images') }}/ignore.png" width="75px" alt="Sad face">
		</div>
	@endif

	@if (count($currentAccountTasks) >= 3)
		<button type="submit" class="btn btn-danger btn-lg btn-block mt-3" disabled>Complete more tasks!</button>
	@else
		<div class="text-center mb-3">
			<form method="POST" action="{{ route('task') }}">
				@csrf

				<button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Generate task</button>
			</form>
		</div>
	@endif

	@if (count($completedAccountTasks) > 0)
		<h2>Completed tasks</h2>

		<table>
			<tr>
				<th></th>
				<th>Task</th>
				<th>Reward</th>
				<th>Completed</th>
			</tr>
			@foreach ($completedAccountTasks as $task)
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
	@endif
@endsection