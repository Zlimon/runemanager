@extends('layouts.layout')

@section('title')
    Link account
@endsection

@section('content')
    <page-account-create :account-types="{{ json_encode($accountTypes) }}"></page-account-create>
@endsection
