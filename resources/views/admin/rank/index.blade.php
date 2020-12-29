@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <h1>Ranks and permissions</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Rank</th>
            <th>Permissions</th>
        </tr>
        @foreach ($roles as $role)
            <tr>
                <td>{{ $role->id }}</td>
                <td>{{ $role->name}}</td>
                <td>{{ $role->}}
            </tr>
        @endforeach
    </table>
@endsection
