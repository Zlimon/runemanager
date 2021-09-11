@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    @if (count(config('settings', [])) )
        <form method="POST" action="{{ route('admin-settings-store') }}">
            @csrf

            <div class="row flex-wrap">
                @foreach(config('settings') as $section => $fields)
                    <div class="col-12 col-md-6">
                    <h2><i class="{{ $fields['icon'] }}"></i> {{ $fields['title'] }}</h2>

                    <p class="text-muted">{{ $fields['desc'] }}</p>

                    @foreach($fields['elements'] as $field)
                        @includeIf('admin.setting.fields.' . $field['type'] )
                    @endforeach

                    <hr>
                    </div>
                @endforeach
            </div>

            <div class="mt-2">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    @endif
@endsection
