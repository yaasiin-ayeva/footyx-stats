@extends('layouts.auth')

@section('page_title', 'Recover Password')

@section('page_content')
	<div class="text-center w-75 m-auto">
		<h4 class="text-dark-50 text-center mt-0 fw-bold">Reset Password</h4>
		<p class="text-muted mb-4">Enter your email address and we'll send you an email with instructions to reset your password.</p>
	</div>

	<form id="form" novalidate>
		<div class="mb-3">
			<label for="email" class="form-label">Email address</label>
			<input class="form-control" type="email" id="email" required="" placeholder="Enter your email">
			<div class="invalid-feedback"></div>
		</div>

		<div class="mb-0 text-center">
			<button class="btn btn-primary" type="submit" id="submit">Reset Password</button>
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
				'email': $('#email'),
				'submit': $('#submit')
			};

			$o.submit.click(function(e) {
				e.preventDefault();

				$o.submit.prop('disabled', true).html(`
						Processing
						<div class="spinner-border spinner-border-sm text-light" role="status"></div>
					`);

				let data = {
					'email': $o.email.val()
				};

				$.ajax({
					'url': '/password/reset/email',
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
							$o.form.find('.is-invalid').removeClass('is-invalid').addClass('is-valid');

							Toast.fire({
								title: 'Email sent',
								text: 'We have e-mailed your password reset link!',
								icon: 'success'
							});
						} else {
							if ('message' in result) {
								Toast.fire({
									title: 'Error!',
									text: result.message,
									icon: 'error'
								});

								if ('fields' in result) {
									for (let field of result.fields) {
										$o[field].removeClass('is-valid').addClass('is-invalid')
											.siblings('.invalid-feedback').empty();
									}
								}
							} else {
								let $first = null;

								for (let name in data) {
									let $obj = $o[name];
									let $feedback = $obj.siblings('.invalid-feedback');

									if (name in result.errors) {
										$feedback.html(result.errors[name][0]);
										$obj.removeClass('is-valid').addClass('is-invalid');

										if (!$first)
											$first = $feedback;
									} else
										$obj.removeClass('is-invalid').addClass('is-valid');
								}

								if ($first) {
									let top = $first.parent().offset().top;
									window.scrollTo({
										top,
										behavior: 'smooth'
									});
								}
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
					'complete': result => {
						$o.submit.html('Reset Password').prop('disabled', false);
					}
				});
			});
		});
	</script>
@endsection