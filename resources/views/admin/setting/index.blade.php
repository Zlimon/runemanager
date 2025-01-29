@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-settings')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <div class="p-4 bg-admin-dark">
                @if (count(config('settings', [])) )
                    <form method="POST" action="{{ route('admin-settings-store') }}">
                        @csrf

                        <div class="row flex-wrap">
                            @foreach(config('settings') as $section => $fields)
                                <div class="col-12 col-md-6">
                                    <h2><i class="{{ $fields['icon'] }}"></i> {{ $fields['title'] }}</h2>
                                    <p>{{ $fields['desc'] }}</p>

                                    <div class="p-4 mb-4 bg-admin-info">
                                        @foreach($fields['elements'] as $field)
                                            @includeIf('admin.setting.fields.' . $field['type'] )
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
