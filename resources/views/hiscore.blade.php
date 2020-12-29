@extends('layouts.layout')

@section('title')
    {{ ucfirst(Helper::collectionAttribute($hiscoreName, "alias")) }}
@endsection

@section('content')
    <link href="{{ asset('css/hiscore.css') }}" rel="stylesheet">

    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <div class="row justify-content-center">
            <a href="{{ route('hiscore', ['skill', 'overall']) }}" class="mr-2">
                <div class="btn button-square background-world-map">
                    <img src="{{ asset('images/skill/overall.png') }}"
                         class="icon"
                         alt="Skills icon"
                         title="Click here to see skills hiscores">
                    <br>
                    <span>Skills</span>
                </div>
            </a>

            <a href="{{ route('hiscore', ['boss', Helper::listBosses()[0]]) }}" class="ml-2">
                <div class="btn button-square background-world-map">
                    <img src="{{ asset('images/boss/boss.png') }}"
                         class="icon"
                         alt="Bosses icon"
                         title="Click here to see boss hiscores">
                    <br>
                    <span>Bosses</span>
                </div>
            </a>
        </div>

        <div id="highscore_top">
            <div class="highscore_selection">
				<span class="selection-top">
					@foreach ($hiscoreListTop as $hiscore)
                        <a href="{{ route('hiscore', [$hiscoreType, $hiscore]) }}">
                            <img src="{{ asset('images/'.$hiscoreType.'/'.$hiscore.'.png') }}"
                                 class="icon"
                                 alt="{{ ucfirst($hiscore) }} {{ $hiscoreType }} icon"
                                 title="Click here to see {{ ucfirst($hiscore) }} hiscores">
                        </a>
                    @endforeach
				</span>
                <div class="mid-part">
                    <h1 class="active middle-icon" style="display: inline-block;">
                        <img src="{{ asset('images/'.$hiscoreType.'/'.$hiscoreName.'.png') }}"
                            class="pixel icon"
                            alt="{{ ucfirst($hiscoreName) }} {{ $hiscoreType }} icon">
                        <br>
                        <span>{{ ucfirst(($hiscoreType === "boss" ? Helper::collectionAttribute($hiscoreName, "alias") : $hiscoreName)) }}</span>
                    </h1>
                </div>
                <span class="selection-bot">
					@foreach ($hiscoreListBottom as $hiscore)
                        <a href="{{ route('hiscore', [$hiscoreType, $hiscore]) }}">
                            <img src="{{ asset('images/'.$hiscoreType.'/'.$hiscore.'.png') }}"
                                 class="icon"
                                 alt="{{ ucfirst($hiscore) }} {{ $hiscoreType }} icon"
                                 title="Click here to see {{ ucfirst($hiscore) }} hiscores">
                        </a>
                    @endforeach
				</span>
            </div>
        </div>

        @if ($accountCount > 0)
            <div class="float-left mt-3">
                <img src="{{ asset('images/'.$hiscoreType.'/'.$hiscoreName.'.png') }}"
                     class="pixel icon"
                     alt="{{ ucfirst($hiscoreName) }} {{ $hiscoreType }} icon"
                     style="width: 7.5rem; height: 7.5rem;">
            </div>

            @if ($hiscoreType == "skill")
                <skillhiscore skill="{{ $hiscoreName }}"></skillhiscore>
            @elseif ($hiscoreType == "boss")
                <bosshiscore boss="{{ $hiscoreName }}"></bosshiscore>
            @endif
        @else
            <div class="text-center py-5">
                <img src="{{ asset('images/ignore.png') }}"
                     class="pixel icon"
                     alt="Sad face">
                <h1>No accounts, no hiscores...</h1>
                <h2 class="text-center">Link an account <a href="{{ route('account-create') }}">here</a>!</h2>
            </div>
        @endif
    </div>
@endsection
