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
            <a class="btn btn-success" href="{{ route('admin-show-newspost', $newsPost->id) }}">Show</a>
        </div>

        <div class="p-2">
            <form method="POST" action="{{ route('admin-delete-newspost', $newsPost) }}">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn btn-danger">Delete newspost</button>
            </form>
        </div>
    @endsection

    <page-admin-news-edit :users="{{ $users }}" :news-post="{{ $newsPost }}" :categories="{{ $newsCategories }}"></page-admin-news-edit>
@endsection
