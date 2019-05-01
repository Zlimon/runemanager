@extends('layouts.layout')

@section('title')
	| {{ __('title.edit-member') }}: {{ $user->name }}
@endsection

@section('content')
	<script>
		function checkRadio() {
			document.getElementById("icon-select").checked = true;
		}

		function fillText() {
			var iconId = document.querySelector('input[name = "icon_id"]:checked').value;
			document.getElementById("icon-text").value = iconId;
		}
	</script>

	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('home') }}">{{ __('title.home') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.edit-member') }}: {{ $user->name }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>

				<div class="main-page-body">
					<form method="POST" action="{{ route('update-user') }}">
						@method('PATCH')
						@csrf

						<div class="form-group row">
							<label for="name" class="col-md-4 col-form-label text-md-right">Profile name</label>

							<div class="col-md-6">
								<input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user->name }}" autocomplete="name" aria-describedby="nameTip" autofocus required="">
								<small id="nameTip" class="form-text text-muted">Your profile name displayed on this site. Not OSRS account.</small>

								@error('name')
									<span class="invalid-feedback" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						<div class="form-group row">
							<label for="email" class="col-md-4 col-form-label text-md-right">Email</label>

							<div class="col-md-6">
								<input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" autocomplete="email" required="">

								@error('email')
									<span class="invalid-feedback" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						@if ($user->icon_id != null)
							<div class="form-group row">
								<label class="col-md-4 col-form-label text-md-right">Current profile icon</label>

								<div class="col-md-6">
									<span>
										<img class="align" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" alt="Current profile icon">
										ID: {{ $user->icon_id }}
									</span>
								</div>
							</div>
						@endif

						<div class="form-group row">
							<label for="icon_id" class="col-md-4 col-form-label text-md-right">Profile icon ID</label>

							<div class="col-md-6">
								<div class="icon-radio">
									<input class="icon-select" type="radio" name="icon_id" id="icon-select" value="{{ $user->icon_id }}" checked>
								</div>

								<input id="icon-text" type="text" class="icon-text form-control @error('icon_id') is-invalid @enderror" name="icon_id" value="{{ old('icon_id', $user->icon_id) }}" aria-describedby="icon_idTip" onfocus="checkRadio()">
								<small id="icon_idTip" class="form-text text-muted">Type in the ID of an icon you wish to display as your profile icon. Search icons <a target="_blank" rel="noopener noreferrer" href="https://www.osrsbox.com/tools/item-search/">here</a></small>

								@error('icon_id')
									<span class="invalid-feedback" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						<div class="form-group row">
							<label for="icon_id" class="col-md-4 col-form-label text-md-right">or pick a random icon</label>

							<div class="col-md-6">
								@foreach ($randomIcons as $icon)
									<label class="icon-radio">
										<input type="radio" name="icon_id" id="icon_id" value="{{ $icon }}" onclick="fillText()">
										<img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $icon }}.png" alt="Random icon">
									</label>
								@endforeach

								@error('icon_id')
									<span class="invalid-feedback" role="alert">
										<strong>{{ $message }}</strong>
									</span>
								@enderror
							</div>
						</div>

						<div class="form-group row mb-0">
							<div class="col-md-8 offset-md-4">
								<button type="submit" class="btn btn-primary">Update</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection