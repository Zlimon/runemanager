@extends('layouts.layout')

@section('title')
	| 
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('member') }}">{{ __('title.member') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ $member->username }}</span>
					<span class="float-right">Next update: </span>
				</div>
				<div class="main-page-body">
					<h1>
						<img class="align" src="https://www.osrsbox.com/osrsbox-db/items-icons/12453.png" alt="{{ $member->username }} avatar image" />
						{{ $member->username }}
					</h1>
					<p class="text-center"><span>Rank: {{ number_format($member->rank) }}</span> | <span>Total XP: {{ number_format($member->xp) }}</span> | <span>Total Level: {{ $member->level }}</span></p>

					@if ($member->private == "true")
						<p class="text-danger">This user is private!</p>
					@else
						<table>
							<tr>
								<th></th>
								<th>Level</th>
								<th>XP</th>
								<th>Rank</th>
							</tr>
							<tr>
								<td>
									<img class="align" src="{{ asset('images/skills/') }}/Overall.png" width="35px" alt="Overall skill icon" />
									Overall
								</td>
								<td>{{ $member->level }}</td>
								<td>{{ number_format($member->xp) }}</td>
								<td>{{ number_format($member->rank) }}</td>
							</tr>

							@php
								$i = 0;
							@endphp
							@foreach ($stats as $skill)
								@foreach ($skill as $skillData)
									<tr>
										<td>
											<a href="{{ route('show-skill', $skills[$i]) }}">
												<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skills[$i]) }}.png" width="35px" alt="{{ ucfirst($skills[$i]) }} skill icon" />
												{{ ucfirst($skills[$i]) }}
											</a>
										</td>
										<td>{{ $skillData->level }}</td>
										<td>{{ number_format($skillData->xp) }}</td>
										<td>{{ number_format($skillData->rank) }}</td>
									</tr>
								@endforeach
								@php
									$i++;
								@endphp
							@endforeach
						</table>
					@endif
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection