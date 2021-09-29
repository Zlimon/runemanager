@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <a class="btn btn-primary" href="{{ route('admin-newspost-edit', $newsPost->id) }}">Edit</a>
        </div>

        <div class="p-2">
            <form method="POST" action="{{ route('admin-newspost-destroy', $newsPost) }}">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn btn-danger">Delete newspost</button>
            </form>
        </div>
    @endsection

    <page-admin-news-show :news-post="{{ $newsPost }}"></page-admin-news-show>
@endsection
