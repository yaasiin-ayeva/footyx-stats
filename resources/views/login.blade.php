@extends('layouts.auth')

@section('page_title', 'Login')

@section('auth_bg')
	<div class="bg-holder" style="background-image:url(/assets/img/generic/23.jpg);background-position: 50% 20%;"></div>
@endsection

@section('page_content')
	<div class="row flex-between-center">
		<div class="col-auto">
			<h3>Login</h3>
		</div>
		<div class="col-auto fs--1 text-600"><span class="mb-0 fw-semi-bold">New User?</span> <span><a href="{{ route('get_register') }}">Create account</a></span></div>
	</div>

	<form>
		<div class="mb-3">
			<label class="form-label" for="email">Email address</label>
			<input class="form-control" id="email" type="email" />
			<div class="invalid-feedback"></div>
		</div>

		<div class="mb-3">
			<div class="d-flex justify-content-between">
				<label class="form-label" for="password">Password</label>
			</div>
			<input class="form-control" id="password" type="password" />
			<div class="invalid-feedback"></div>
		</div>
		
		<div class="row flex-between-center">
			<div class="col-auto">
				<div class="form-check mb-0">
					<input class="form-check-input" type="checkbox" id="remember_token" />
					<label class="form-check-label mb-0" for="remember_token">Remember me</label>
				</div>
			</div>
			<div class="col-auto"><a class="fs--1" href="{{ route('get_password_reset_email_form') }}">Forgot Password?</a></div>
		</div>
		
		<div class="mb-3">
			<button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit" id="submit">Log in</button>
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
				'form': $('#form'),

				'email': $('#email'),
				'password': $('#password'),
				'remember_token': $('#remember_token'),

				'submit': $('#submit')
			};

			$o.submit.click(function(e) {
				e.preventDefault();

				$o.submit.prop('disabled', true).html(`
					Connecting...
				`);

				let data = {
					'email': $o.email.val(),
					'password': $o.password.val(),
					'remember_token': $o.remember_token.prop('checked')
				};

				$.ajax({
					'url': "{{ route('login') }}",
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
							$o.form.find('.is-invalid').removeClass('is-invalid').addClass('is-valid');

							Toast.fire({
								title: 'Connection established!',
								icon: 'success'
							}).then(() => {
								location = "{{ route('dashboard') }}";
							});
						} else {
							if (result.message) {
                Toast.fire({
                  title: 'Error!',
                  text: result.message,
                  icon: 'error'
                });

                if('fields' in result) {
                  for(let field of result.fields) {
                    $o[field].removeClass('is-valid').addClass('is-invalid')
                      .siblings('.invalid-feedback').empty();
                  }
                }
							}
							
							if ('errors' in result) {
								let $first = null;

              	for(let name in data) {
									let $obj = $o[name];
									let $feedback = $obj.siblings('.invalid-feedback');

									if (name in result.errors) {
										$feedback.html(result.errors[name][0]);
										$obj.removeClass('is-valid').addClass('is-invalid');

										if (!$first) {
											$first = $feedback;
										}
									}
									else {
										$obj.removeClass('is-invalid').addClass('is-valid');
									}
								}

								if ($first) {
									let top = $first.parent().offset().top;
									window.scrollTo({top, behavior: 'smooth'});
								}
							}
						}
					},
					error: result => {
						console.log(result.responseJSON.message);
					},
					complete: result => {
						$o.submit.html('Log in').prop('disabled', false);
					}
				});
			});
		});
	</script>
@endsection