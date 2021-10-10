@extends('layouts.layout')

@section('title')
    Link account
@endsection

@section('content')
    <page-account-auth-create :account-types="{{ json_encode($accountTypes) }}"></page-account-auth-create>
@endsection
