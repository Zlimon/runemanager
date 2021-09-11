@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <form method="post" action="{{ route('admin-settings-store') }}" class="form-horizontal" role="form">
                    {!! csrf_field() !!}

                    @if(count(config('settings', [])) )
                        @foreach(config('settings') as $section => $fields)
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h2><i class="{{ $fields['icon'] }}"></i> {{ $fields['title'] }}</h2>
                                </div>

                                <div class="panel-body">
                                    <p class="text-muted">{{ $fields['desc'] }}</p>
                                </div>

                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-7  col-md-offset-2">
                                            @foreach($fields['elements'] as $field)
                                                @includeIf('admin.setting.fields.' . $field['type'] )
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                        @endforeach
                    @endif

                    <div class="row m-b-md">
                        <div class="col-md-12">
                            <button class="btn-primary btn">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
