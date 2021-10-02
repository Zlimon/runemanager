@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    <page-admin-account-show :account="{{ $account }}"></page-admin-account-show>
@endsection
