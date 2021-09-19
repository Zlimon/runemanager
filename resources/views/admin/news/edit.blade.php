@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    @section('navigation')
        <form method="POST" action="{{ route('admin-delete-newspost', $newsPost) }}" class="p-2">
            @method('DELETE')
            @csrf

            <button type="submit" class="btn btn-danger">Delete newspost</button>
        </form>
    @endsection

    <newsupdate :users="{{ $users }}" :news-post="{{ $newsPost }}" :categories="{{ $newsCategories }}"></newsupdate>
@endsection
