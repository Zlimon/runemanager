@extends('layouts.layout')

@section('title')
	{{ ucfirst($skillName) }} {{ __('title.hiscore') }}
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
				<span class="active middle-icon" style="display: inline-block;"><img class="pixel middle-img-icon" src="{{ asset('images/skills/') }}/{{ ucfirst($skillName) }}.png" alt="{{ ucfirst($skillName) }} skill icon"><h1>{{ ucfirst($skillName) }}</h1></span>
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

	<div class="float-left mt-3">
		<span class="middle-icon">
			<img class="pixel middle-img-icon" style="width: 150px; height: 150px;" src="{{ asset('images/skills/') }}/{{ ucfirst($skillName) }}.png" alt="{{ ucfirst($skillName) }} skill icon">
		</span>
	</div>

	<hiscore skill="{{ $skillName }}"></hiscore>
@endsection