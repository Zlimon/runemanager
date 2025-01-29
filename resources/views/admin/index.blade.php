@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-dashboard')
    active
@endsection

@section('content')
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
        <div class="col">
            <div class="card h-100 bg-admin-dark">
                <div class="card-body">
                    <div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
                    <h5 class="card-title">Total users</h5>
                    <p class="card-text"><strong>{{ $users }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark">
                <div class="card-body">
                    <div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
                    <h5 class="card-title">Total users</h5>
                    <p class="card-text"><strong>{{ $users }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark">
                <div class="card-body">
                    <div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
                    <h5 class="card-title">Total users</h5>
                    <p class="card-text"><strong>{{ $users }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark">
                <div class="card-body">
                    <div class="float-left"><i class="fas fa-users" style="font-size: 4em; font-weight: 900;"></i></div>
                    <h5 class="card-title">Total users</h5>
                    <p class="card-text"><strong>{{ $users }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        <div class="col">
            <div class="card h-100 bg-admin-dark border-0">
                <div class="card-header bg-admin-info">
                    Featured
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark border-0">
                <div class="card-header bg-admin-info">
                    Featured
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a short card.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark border-0">
                <div class="card-header bg-admin-info">
                    Featured
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-admin-dark border-0">
                <div class="card-header bg-admin-info">
                    Featured
                </div>
                <div class="card-body">
                    <h5 class="card-title">Card title</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
