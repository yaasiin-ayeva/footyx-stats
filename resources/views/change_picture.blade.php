@extends('layouts.app')

@section('page_title', 'Change Picture')

@section('page_content')
	<form id="form" class="row" novalidate>
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					<div class="row gy-3 mt-0">
						<div class="col-12 text-center">
							<img src="/storage/thumbnails/{{ Auth::user()->image }}" alt="user-image" class="rounded-circle">
						</div>

						<div class="col-12">
							<label for="image" class="form-label">Select your new image</label>
							<input class="form-control" type="file" id="image" accept="image/*">
							<div class="invalid-feedback"></div>
						</div>

						<div class="col-12 text-center">
							<button class="btn btn-primary" type="button" id="submit">Submit</button>

							@if(Auth::user()->image != 'no_image.png')
								<button class="btn btn-danger" type="button" id="remove">Remove</button>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection

@section('page_scripts')
	@parent
	<script type="text/javascript">
		$(() => {
			let $o = {
				'form': $('#form'),
				'image': $('#image'),
				'submit': $('#submit'),
				'remove': $('#remove'),
			};

			let lockCount = 0;

			$o.submit.click(function(e) {
				e.preventDefault();

				lock();

				$o.submit.html('Submitting...');

				let fd = new FormData();

				if($o.image.get(0).files.length) {
					fd.append('image', $o.image.get(0).files[0]);
				}
				
				$.ajax({
					'url': "{{ route('change_picture') }}",
					'type': 'POST',
					'data': fd,
					'dataType': 'json',
					'contentType': false,
					'processData': false,
					'success': result => {
						if (result.status == 'success') {
							Toast.fire('Picture changed!', '', 'success').then(() => {
								location = "{{ route('dashboard') }}";
							});

							unlock();
						} else {
							if (result.message) {
								Toast.fire('Error!', result.message, 'error');
							}

							if('errors' in result) {
								display_errors(result.errors, $o, ['image'], 80);
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
					'complete': result => {
						unlock();
						$o.submit.html('Submit');
					}
				});
			});

			$o.remove.click(function(e) {
				e.preventDefault();

				Swal.fire({
					title: 'Do you want to remove your picture?',
					html: 'The default picture will replace it',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Remove',
				}).then(result => {
					if (result.isConfirmed) {
						remove_picture();
					}
				});
			});

			function remove_picture() {
				lock();

				$o.remove.html('Removing...');

				$.ajax({
					'url': "{{ route('remove_picture') }}",
					'type': 'POST',
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
							Toast.fire(result.message, '', 'success').then(() => {
								location = "{{ route('dashboard') }}";
							});

							unlock();
						} else {
							if (result.message) {
								Toast.fire('Error!', result.message, 'error');
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
					'complete': result => {
						unlock();
						$o.remove.html('Remove');
					}
				});
			}

			function lock() {
				if(!lockCount++) {
					$o.submit.prop('disabled', true);
					$o.remove.prop('disabled', true);
				}
			}
			
			function unlock() {
				if(!--lockCount) {
					$o.submit.prop('disabled', false);
					$o.remove.prop('disabled', false);
				}
			}
		});
	</script>
@endsection