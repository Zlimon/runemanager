@extends('layouts.layout')

@section('title')
    {{ $hiscore->name }}
@endsection

@section('content')
    <link href="{{ asset('css/hiscore.css') }}" rel="stylesheet">

    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <div class="row justify-content-center">
            <a href="{{ route('hiscore', ['skill', 'total']) }}" class="mx-2">
                <div class="btn button-square background-world-map">
                    <img src="{{ asset('images/skill/total.png') }}"
                         class="pixel icon"
                         alt="Skills icon"
                         title="Click here to see the skills hiscores">
                    <br>
                    <span>Skills</span>
                </div>
            </a>

            <a href="{{ route('hiscore', ['boss', Helper::listBosses()[0]]) }}" class="mx-2">
                <div class="btn button-square background-world-map">
                    <img src="{{ asset('images/boss/boss.png') }}"
                         class="pixel icon"
                         alt="Bosses icon"
                         title="Click here to see the boss hiscores">
                    <br>
                    <span>Bosses</span>
                </div>
            </a>

            @if (count(Helper::listNpcs()) > 0)
                <a href="{{ route('hiscore', ['npc', Helper::listNpcs()[0]]) }}" class="mx-2">
                    <div class="btn button-square background-world-map">
                        <img src="{{ asset('images/boss/boss.png') }}"
                             class="pixel icon"
                             alt="Monsters icon"
                             title="Click here to see the monster hiscores">
                        <br>
                        <span>Monsters</span>
                    </div>
                </a>
            @endif

            @if (count(Helper::listClues()) > 0)
                <a href="{{ route('hiscore', ['clue', Helper::listClues()[0]]) }}" class="mx-2">
                    <div class="btn button-square background-world-map">
                        <img src="{{ asset('images/clue/clue.png') }}"
                             class="pixel icon"
                             alt="Treasure Trails icon"
                             title="Click here to see the treasure trails hiscores">
                        <br>
                        <span>Treasure Trails</span>
                    </div>
                </a>
            @endif
        </div>

        <div id="highscore_top">
            <div class="highscore_selection">
				<span class="selection-top">
					@foreach ($hiscoreListTop as $hiscoreTop)
                        <a href="{{ route('hiscore', [$hiscoreCategory, $hiscoreTop]) }}">
                            <img src="{{ asset(($hiscoreCategory == "skill" ? "storage/resource-pack" : "images").'/'.$hiscoreCategory.'/'.str_replace('-', '_', $hiscoreTop).'.png') }}"
                                 class="icon"
                                 title="Click here to see {{ ucfirst($hiscoreTop) }} hiscores">
                        </a>
                    @endforeach
				</span>
                <div class="mid-part">
                    <h1 class="active middle-icon" style="display: inline-block;">
                        <img src="{{ asset(($hiscoreCategory == "skill" ? "storage/resource-pack" : "images").'/'.$hiscoreCategory.'/'.str_replace('-', '_', $hiscore->slug).'.png') }}"
                            class="pixel icon">
                        <br>
                        <span>{{ $hiscore->name }}</span>
                    </h1>
                </div>
                <span class="selection-bot">
					@foreach ($hiscoreListBottom as $hiscoreBottom)
                        <a href="{{ route('hiscore', [$hiscoreCategory, $hiscoreBottom]) }}">
                            <img src="{{ asset(($hiscoreCategory == "skill" ? "storage/resource-pack" : "images").'/'.$hiscoreCategory.'/'.str_replace('-', '_', $hiscoreBottom).'.png') }}"
                                 class="icon"
                                 title="Click here to see {{ ucfirst($hiscoreBottom) }} hiscores">
                        </a>
                    @endforeach
				</span>
            </div>
        </div>

        @if ($accountCount > 0)
            @if ($hiscoreCategory == "skill")
                <skillhiscore skill="{{ $hiscore->name }}"></skillhiscore>
            @elseif ($hiscoreCategory == "boss")
                <bosshiscore boss="{{ $hiscore->name }}"></bosshiscore>
            @elseif ($hiscoreCategory == "npc")
                <npchiscore npc="{{ $hiscore->name }}"></npchiscore>
            @elseif ($hiscoreCategory == "clue")
                <cluehiscore clue="{{ $hiscore->name }}"></cluehiscore>
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
