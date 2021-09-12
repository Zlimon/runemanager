@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <page-admin-setting-resource-pack :resource-packs="{{ $resourcePacks }}" :current-resource-pack="{{ $currentResourcePack }}"></page-admin-setting-resource-pack>
@endsection
