@extends('layouts.layout')

@section('title')
    Authentication
@endsection

@section('content')
    <page-account-auth-index :auth-status="{{ $authStatus }}" :account-types="{{ json_encode($accountTypes) }}"></page-account-auth-index>
@endsection
