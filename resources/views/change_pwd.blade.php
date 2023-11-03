@extends('layouts.app')

@section('page_title', 'Change Password')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
					<div class="row gy-3 mt-0">
            <div class="col-12">
              <label class="label" for="password">Current password</label>
              <input type="password" id="password" maxlength="20" class="form-control">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12">
              <label class="label" for="new_password">New password</label>
              <input type="password" id="new_password" maxlength="20" class="form-control">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12">
              <label class="label" for="new_password_confirmation">Confirmation</label>
              <input type="password" id="new_password_confirmation" maxlength="20" class="form-control">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 text-center">
							<button class="btn btn-primary" id="submit">Submit</button>
						</div>
          </div>            
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page_scripts')
  @parent
  <script type="text/javascript">
    $(() => {
      let $o = {
				'password': $('#password'),
				'new_password': $('#new_password'),
				'new_password_confirmation': $('#new_password_confirmation'),
				'submit': $('#submit'),
			};

      let lockCount = 0;

			$o.submit.click(function(e) {
				e.preventDefault();

				lock();

				$o.submit.html('Submitting...');

				let data = {
          'password': $o.password.val(),
          'new_password': $o.new_password.val(),
          'new_password_confirmation': $o.new_password_confirmation.val()
        };

				$.ajax({
					'url': "{{ route('change_pwd') }}",
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
							Toast.fire('Password changed!', '', 'success').then(() => {
								location = "{{ route('dashboard') }}";
							});

							unlock();
						} else {
							if (result.message) {
								Toast.fire('Error!', result.message, 'error');
							}

							if('errors' in result) {
								display_errors(result.errors, $o, ['password', 'new_password', 'new_password_confirmation'], 80);
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

      function lock() {
				if(!lockCount++) {
					$o.submit.prop('disabled', true);
				}
			}
			
			function unlock() {
				if(!--lockCount) {
					$o.submit.prop('disabled', false);
				}
			}
    });
  </script>
@endsection