@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	<h1>News</h1>

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
				<td>{{ $news->category->category }}</td>
				<td>{{ $news->user->name }}</td>
				<td>{{ \Carbon\Carbon::parse($news->created_at)->format('d. M Y H:i') }}</td>
				<td>
					<a class="btn btn-success mr-2" href="{{ route('admin-show-newspost', $news->id) }}">Show</a>
					<a class="btn btn-primary mr-2" href="{{ route('admin-edit-newspost', $news->id) }}">Edit</a>
					<form method="POST" action="{{ route('admin-delete-newspost', $news->id) }}">
						@method('DELETE')
						@csrf

						<button type="submit" class="btn btn-danger">Delete</button>
					</form>
				</td>
			</tr>
		@endforeach
	</table>
@endsection