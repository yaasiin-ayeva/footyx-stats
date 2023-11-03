@extends('layouts.auth')

@section('page_title', 'Register')

@section('auth_bg')
	<div class="bg-holder" style="background-image:url(/assets/img/generic/21.jpg);background-position: 50% 20%;"></div>
@endsection

@section('page_content')
	<div class="row flex-between-center">
		<div class="col-auto">
			<h3>Register</h3>
		</div>
		<div class="col-auto fs--1 text-600"><span class="mb-0 fw-semi-bold">Already User?</span> <span><a href="{{ route('get_login') }}">Login</a></span></div>
	</div>

	<form novalidate>
		<div class="mb-3">
			<label class="form-label" for="name">Name</label>
			<input class="form-control" type="text" autocomplete="on" id="name" />
			<div class="invalid-feedback"></div>
		</div>

		<div class="mb-3">
			<label class="form-label" for="email">Email address</label>
			<input class="form-control" type="email" autocomplete="on" id="email" />
			<div class="invalid-feedback"></div>
		</div>
		
		<div class="row gx-2">
			<div class="mb-3 col-sm-6">
				<label class="form-label" for="password">Password</label>
				<input class="form-control" type="password" autocomplete="on" id="password" />
				<div class="invalid-feedback"></div>
			</div>
			
			<div class="mb-3 col-sm-6">
				<label class="form-label" for="password_confirmation">Confirm Password</label>
				<input class="form-control" type="password" autocomplete="on" id="password_confirmation" />
				<div class="invalid-feedback"></div>
			</div>
		</div>

		<div class="mb-3">
			<label for="image" class="form-label">Profile picture (optional)</label>
			<input class="form-control" type="file" id="image" accept="image/*">
			<div class="invalid-feedback"></div>
		</div>
		
		<div class="form-check">
			<input class="form-check-input" type="checkbox" id="agree" />
			<label class="form-label" for="agree">I accept the <a href="#!">terms </a>and <a href="#!">privacy policy</a></label>
			<div class="invalid-feedback"></div>
		</div>

		<div class="mb-3">
			<button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit" id="submit">Register</button>
		</div>
	</form>

	{{-- <div class="position-relative mt-4">
		<hr class="bg-300" />
		<div class="divider-content-center">or log in with</div>
	</div>

	<div class="row g-2 mt-2">
		<div class="col-sm-6"><a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#"><span class="fab fa-google-plus-g me-2" data-fa-transform="grow-8"></span> google</a></div>
		
		<div class="col-sm-6"><a class="btn btn-outline-facebook btn-sm d-block w-100" href="#"><span class="fab fa-facebook-square me-2" data-fa-transform="grow-8"></span> facebook</a></div> --}}
	</div>
@endsection

@section('page_scripts')
	<script type="text/javascript">
		$(() => {
			let $o = {
				'name': $('#name'),
				'email': $('#email'),
				'password': $('#password'),
				'password_confirmation': $('#password_confirmation'),
				'image': $('#image'),
				'agree': $('#agree'),
				'submit': $('#submit')
			};

			let lockCount = 0;

			$o.submit.click(function(e) {
				e.preventDefault();

				lock();

				$o.submit.html(`Registering...`);

				let fd = new FormData();

        fd.append('name', $o.name.val());
        fd.append('email', $o.email.val());
        fd.append('password', $o.password.val());
        fd.append('password_confirmation', $o.password_confirmation.val());
        
        if($o.image.get(0).files.length) {
					fd.append('image', $o.image.get(0).files[0]);
				}

				fd.append('agree', $o.agree.prop('checked'));

				$.ajax({
					'url': "{{ route('register') }}",
					'type': 'POST',
					'data': fd,
					'dataType': 'json',
					'contentType': false,
					'processData': false,
					'success': result => {
						if (result.status == 'success') {
							Toast.fire({
                'title': result.message,
                'icon': 'success'
              }).then(() => {
								location = "{{ route('dashboard') }}";
							});
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
							
							if ('errors' in result) {
								display_errors(result.errors, $o, ['name', 'email', 'password', 'password_confirmation', 'image', 'agree']);
							}
						}
					},
					error: result => {
						console.log(result.responseJSON.message);
					},
					complete: result => {
						unlock();
						$o.submit.html('Register');
					}
				});
			});

			function lock() {
        if (!lockCount++) {
          $o.submit.prop('disabled', true);
        }
      }

      function unlock() {
        if (lockCount > 0) {
          if (!--lockCount) {
            $o.submit.prop('disabled', false);
          }
        }
      }
		});
	</script>
@endsection