@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-settings')
    active
@endsection

@section('content')
    <page-admin-setting-resource-pack :resource-packs-prop="{{ $resourcePacks }}" :current-resource-pack-prop="{{ $currentResourcePack }}"></page-admin-setting-resource-pack>
@endsection
