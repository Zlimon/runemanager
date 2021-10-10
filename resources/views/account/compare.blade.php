@extends('layouts.layout')

@section('title')
    TITLE
@endsection

@section('content')
    <page-account-compare account-one-username="{{ $accountOneUsername }}" account-two-username="{{ $accountTwoUsername }}"></page-account-compare>
@endsection
