@extends('layouts.layout')

@section('title')
	{{ __('title.create-member') }}
@endsection

@section('content')
	<h2>Account authentication status for:</h2>

	<h1>{{ $authStatus->username }}</h1>

	<div class="form-group row">
		<label for="status" class="col-md-4 text-md-right">Status:</label>

		<div class="col-md-6">
			<strong>{{ ucfirst($authStatus->status) }}</strong>
		</div>
	</div>

	<div class="form-group row">
		<label for="code" class="col-md-4 text-md-right">Code:</label>

		<div class="col-md-6">
			<span>{{ $authStatus->code }}</span>
		</div>
	</div>

	<form method="POST" action="{{ route('update-account-auth') }}">
		@csrf
		@method('PATCH')

		<div class="form-group row">
			<label for="account_type" class="col-md-4 text-md-right">Account type:</label>

			<div class="col-md-8">
				<span><img class="align" src="{{ asset('images') }}/{{ $authStatus->account_type }}.png" alt="{{ Helper::formatAccountTypeName($authStatus->account_type) }} icon" title="You have currently picked {{ Helper::formatAccountTypeName($authStatus->account_type) }} as account type for your account"> {{ Helper::formatAccountTypeName($authStatus->account_type) }}</span>

				<div class="row mt-2">
					<div class="col-md-5">
						<select id="account_type" class="form-control" name="account_type">
							@foreach (Helper::listAccountTypes() as $accountType)
								<option value="{{ $accountType }}">{{ Helper::formatAccountTypeName($accountType) }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-4">
						<button type="submit" class="btn btn-primary">Update</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<form method="POST" action="{{ route('delete-account-auth') }}">
		@csrf
		@method('DELETE')

		<div class="form-group row mb-0">
			<div class="col-md-8 offset-md-4">
				<button type="submit" class="btn btn-danger">Delete</button>
			</div>
		</div>
	</form>
@endsection