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

        <form method="POST" action="{{ route('admin-create-newspost-category') }}" class="col-8 col-md-2">
            @csrf

            <div class="row">
                <div class="input-group">
                    <input type="text"
                           id="category"
                           name="category"
                           class="form-control @error('category') is-invalid @enderror"
                           placeholder="Create new category"
                           required>
                    <button class="btn btn-primary">Create</button>
                </div>
            </div>

            @error('category')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </form>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>News ID</th>
                    <th>Title</th>
                    <th class="d-none d-md-table-cell">Shortstory</th>
                    <th class="d-none d-md-table-cell">Category</th>
                    <th>Author</th>
                    <th class="d-none d-md-table-cell">Posted</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($newsPosts as $news)
                    <tr>
                        <th scope="row">{{ $news->id }}</th>
                        <td>{{ $news->title }}</td>
                        <td class="d-none d-md-table-cell">{{ $news->shortstory }}</td>
                        <td class="d-none d-md-table-cell">{{ $news->newsCategory->category }}</td>
                        <td>{{ $news->user->name }}</td>
                        <td class="d-none d-md-table-cell">{{ \Carbon\Carbon::parse($news->created_at)->format('d. M Y H:i') }}</td>
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
            </tbody>
        </table>
    </div>
@endsection
