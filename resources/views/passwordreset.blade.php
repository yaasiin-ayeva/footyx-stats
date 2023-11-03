@extends('layouts.auth')

@section('page_title', 'Reset password')

@section('page_content')
	<div class="text-center w-75 m-auto">
		<h4 class="text-dark-50 text-center pb-0 fw-bold">Reset password</h4>
		<p class="text-muted mb-4">Create your new password.</p>
	</div>

	<form id="form" novalidate>
		<div class="mb-3">
			<label for="password" class="form-label">New password</label>
			<div class="input-group input-group-merge">
				<input type="password" id="password" class="form-control" placeholder="Enter your new password">
				<div class="input-group-text" data-password="false">
					<span class="password-eye"></span>
				</div>
				<div class="invalid-feedback"></div>
			</div>
		</div>

		<div class="mb-3">
			<label for="password_confirmation" class="form-label">Confirmation</label>
			<div class="input-group input-group-merge">
				<input type="password" id="password_confirmation" class="form-control" placeholder="Confirm your new password">
				<div class="input-group-text" data-password="false">
					<span class="password-eye"></span>
				</div>
				<div class="invalid-feedback"></div>
			</div>
		</div>

		<div class="mb-3 mb-0 text-center">
			<button class="btn btn-primary" type="submit" id="submit">Reset</button>
		</div>

	</form>
@endsection

@section('page_after_content')
	<div class="row mt-3">
			<div class="col-12 text-center">
					<p class="text-muted">Back to <a href="/login" class="text-muted ms-1"><b>Log In</b></a></p>
			</div>
	</div>
@endsection

@section('page_scripts')
	<script type="text/javascript">
		$(() => {
			let $o = {
				'form': $('#form'),
				'password': $('#password'),
				'password_confirmation': $('#password_confirmation'),
				'submit': $('#submit')
			};

			$o.submit.click(function(e) {
				e.preventDefault();

				$o.submit.prop('disabled', true).html(`
					Processing
					<div class="spinner-border spinner-border-sm text-light" role="status"></div>
				`);

				let data = {
					'email': '{{$email}}',
					'token': '{{$token}}',
					'password': $o.password.val(),
					'password_confirmation': $o.password_confirmation.val()
				};

				$.ajax({
					'url': '/password/reset',
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if(result.status == 'success') {
							$o.form.find('.is-invalid').removeClass('is-invalid').addClass('is-valid');

							Toast.fire({
								title: 'Password changed',
								// text: 'Click OK to be redirected to the login page',
								icon: 'success'
							}).then(() => {
								location = '/login';
							});
						}
						else {
							let message = null;

							if ('email' in result.errors)
								message = result.errors['email'][0];
							else if('token' in result.errors)
								message = result.errors['token'][0];

							if(message)
								Toast.fire('Error!', message, 'error');
							 
							let $first = null;

							for(let name of ['password', 'password_confirmation']) {
								let $obj = $o[name];
								let $feedback = $obj.siblings('.invalid-feedback');

								if (name in result.errors) {
									$feedback.html(result.errors[name][0]);
									$obj.removeClass('is-valid').addClass('is-invalid');

									if (!$first)
										$first = $feedback;
								}
								else
									$obj.removeClass('is-invalid').addClass('is-valid');
							}

							if ($first) {
								let top = $first.parent().offset().top;
								window.scrollTo({top, behavior: 'smooth'});
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
					'complete': result => {
						$o.submit.html('Reset').prop('disabled', false);
					}
      	});

      });
		});
	</script>
@endsection