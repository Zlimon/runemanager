@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-news')
    active
@endsection

@section('content')
    <div class="d-flex justify-content-between">
        <div class="col">
            <h1>News</h1>
        </div>

        <form method="POST" action="{{ route('admin-create-newspost-category') }}" class="col-4">
            @csrf

            <div class="search">
                <i class="fa fa-newspaper"></i>
                <input type="text"
                       id="category"
                       name="category"
                       class="form-control @error('category') is-invalid @enderror"
                       placeholder="Create new category"
                       required>
                <button class="btn btn-primary">Create</button>
            </div>

            @error('category')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </form>
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

<style>
    .search {
        position: relative;
        box-shadow: 0 0 40px rgba(51, 51, 51, .1);
    }

    .search input {
        height: 60px;
        text-indent: 25px;
        border: 2px solid #d6d4d4;
    }

    .search input:focus {
        box-shadow: none;
        border: 2px solid blue;
    }

    .search .fa {
        position: absolute;
        top: 20px;
        left: 16px;
        color: black;
    }

    .search button {
        position: absolute;
        top: 5px;
        right: 5px;
        height: 50px;
        width: 110px;
    }
</style>
