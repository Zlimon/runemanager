@extends('layouts.layout')

@section('title')
	{{ __('title.skill') }}
@endsection

@section('content')
	<div class="wide" id="highscore_top">
		<div class="highscore_selection">
			<span class="selection-top">
				@foreach ($skillsTop as $skill)
					<a class="middle-icon" href="{{ route('show-skill', $skill) }}"><img class="middle-img-icon" src="{{ asset('images/skills/') }}/{{ ucfirst($skill) }}.png" alt="{{ ucfirst($skill) }} skill icon"></a>
				@endforeach
			</span>
			<div class="mid-part">
				<span class="active middle-icon" style="display: inline-block;"><img class="pixel middle-img-icon" src="{{ asset('images/skills/') }}/{{ ucfirst($skillname) }}.png" alt="{{ ucfirst($skillname) }} skill icon"><h1>{{ ucfirst($skillname) }}</h1></span>
				@foreach ($skills as $skill)
					<span class="middle-icon"><img class="pixel middle-img-icon" src="{{ asset('images/skills/') }}/{{ ucfirst($skill) }}.png" alt="{{ ucfirst($skill) }} skill icon"><h1>{{ ucfirst($skill) }}</h1></span>
				@endforeach
			</div>
			<span class="selection-bot">
				@foreach ($skillsBottom as $skill)
					<a class="middle-icon" href="{{ route('show-skill', $skill) }}"><img class="middle-img-icon" src="{{ asset('images/skills/') }}/{{ ucfirst($skill) }}.png" alt="{{ ucfirst($skill) }} skill icon"></a>
				@endforeach
			</span>
		</div>
	</div>

	<script>
		$(".highscore_selection > span > a").mouseover(function() {
			$(".highscore_selection > span > a.active").toggleClass("active inactive");
			var index = $(".highscore_selection > span > a").index($(this));
			$(".mid-part > span").hide();
			$(".mid-part > span:eq(" + index + ")").css('display', 'inline-block');
		});
		$(".highscore_selection > span > a").mouseleave(function() {
			$(".highscore_selection > span > a.inactive").toggleClass("inactive active");
			$(".mid-part > span").hide();
			$(".mid-part > span.active").css('display', 'inline-block');
		});
	</script>

	<div class="float-left mt-3">
		<span class="middle-icon">
			<img class="pixel middle-img-icon" style="width: 150px; height: 150px;" src="{{ asset('images/skills/') }}/{{ ucfirst($skillname) }}.png" alt="{{ ucfirst($skillname) }} skill icon">
		</span>
	</div>

	<div class="float-left mt-3 ml-3">
		<h1 class="text-left">{{ ucfirst($skillname) }}</h1>

		<span>Total XP: <strong>{{ number_format($sumTotalXp) }}</strong></span>
		<br>
		<span>Average Level: <strong>{{ round($averageTotalLevel) }}</strong></span>
		<br>
		<span>Maxed: <strong>{{ $totalMaxLevel }}</strong></span>
	</div>

	<table>
		<tr>
			<th>Rank</th>
			<th>Member</th>
			<th>Total Level</th>
			<th>Total XP</th>
			<th>Hiscore Rank</th>
		</tr>
		@php
			$rankCounter = 1;
		@endphp
		@foreach ($hiscores as $hiscore)
			<tr>
				<td>{{ $rankCounter }}</td>
				<td><a href="{{ route('show-member', ($skillname == 'overall' ? $hiscore->id : $hiscore->account_id)) }}">{{ $hiscore->username }}</a></td>
				<td>{{ $hiscore->level }}</td>
				<td>{{ number_format($hiscore->xp) }}</td>
				<td>{{ number_format($hiscore->rank) }}</td>
			</tr>
			@php
				$rankCounter++;
			@endphp
		@endforeach
	</table>
@endsection