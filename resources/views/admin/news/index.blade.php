@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <div class="d-flex justify-content-between">
        <div>
            <h1>News</h1>
        </div>

        <div>
            <h5>Create new category</h5>

            <form method="POST" action="{{ route('admin-create-newspost-category') }}" class="form-inline">
                @csrf

                <label class="sr-only" for="inlineFormInputName2">Name</label>
                <input type="text" class="form-control mb-2 mr-sm-2 @error('category') is-invalid @enderror" name="category" id="category" value="{{ old('user') }}" placeholder="Category name">

                <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </form>
        </div>
    </div>

    <table>
        <tr>
            <th>News ID</th>
            <th>Title</th>
            <th>Shortstory</th>
            <th>Category</th>
            <th>Author</th>
            <th>Posted</th>
            <th>Actions</th>
        </tr>

        @foreach ($newsPosts as $news)
            <tr>
                <td>{{ $news->id }}</td>
                <td>{{ $news->title }}</td>
                <td>{{ $news->shortstory }}</td>
                <td>{{ $news->newsCategory->category }}</td>
                <td>{{ $news->user->name }}</td>
                <td>{{ \Carbon\Carbon::parse($news->created_at)->format('d. M Y H:i') }}</td>
                <td>
                    <a class="btn btn-success mr-2" href="{{ route('admin-show-newspost', $news->id) }}">Show</a>
                    <a class="btn btn-primary mr-2" href="{{ route('admin-edit-newspost', $news->id) }}">Edit</a>
                    <form method="POST" action="{{ route('admin-delete-newspost', $news) }}">
                        @method('DELETE')
                        @csrf

                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endsection
