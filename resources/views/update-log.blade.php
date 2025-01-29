@extends('layouts.layout')

@section('title')
    {{ __('title.update-log') }}
@endsection

@section('content')
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3">
        <h1>{{ __('title.update-log') }}</h1>

        @foreach ($updates as $log)
            <p>{{ $log->username }} <i
                    class="fas fa-long-arrow-alt-right"></i> {{ \Carbon\Carbon::parse($log->updated_at)->format('d. M Y H:i') }}
            </p>
        @endforeach
    </div>
@endsection
