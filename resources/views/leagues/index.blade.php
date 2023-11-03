@extends('layouts.app')

@section('page_title', 'Leagues in ' . $cnt->name)

@section('page_content')
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">Add a league in {{ $cnt->name }}</div>
        <div class="card-body">
          <form id="form" class="row gy-3" novalidate>
            <div class="col-12">
              <label for="name" class="form-label">Name of the league</label>
              <input class="form-control" type="text" id="name" maxlength="63" placeholder="Enter the name of the league">
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 text-center">
              <button class="btn btn-primary" type="submit" id="submit" data-type="add">Add</button>
              <button class="btn btn-outline-primary" id="clear">Clear</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">List of leagues in {{ $cnt->name }}</div>
        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              
            </tbody>
          </table>
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
        'name': $('#name'),
				'submit': $('#submit'),
				'clear': $('#clear'),
        
				'table': $('#table'),
			};

      let selectedRow = null;
      let datatable = null;

      $o.submit.click(function(e) {
				e.preventDefault();

        lock();

        if($o.submit.data('type') == 'add') {
          add();
        }
        else {
          update();
        }
			});

      $o.clear.click(function(e) {
        e.preventDefault();

        $o.name.val('').removeClass('is-valid is-invalid');
        
        $o.submit.data('type', 'add').html('Add');
      });

      datatable = $o.table
      .on('click', '.edit', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        let data = selectedRow.data();

        $o.name.removeClass('is-valid is-invalid').val(data.name);

        $o.submit.data('type', 'update').html('Update');

        let top = $o.form.offset().top - 80;
        window.scrollTo({top, behavior: 'smooth'});
      })
      .on('click', '.delete', function(e) {
        if($o.submit.data('type') == 'update') {
          $o.clear.click();
        }

        selectedRow = datatable.row($(this).closest('tr'));

        Swal.fire({
          title: `Do you want to delete ${selectedRow.data().name}?`,
          text: `This action will also delete all files loaded for that league`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Delete',
        }).then(result => {
          if (result.isConfirmed) {
            _delete();
          }
        });
      })
      .on('preDraw.dt', function() {
        $o.table.find('[title]').tooltip('dispose');
      })
      .on('draw.dt', function() {
        $o.table.find('[title]').tooltip();
      })
      .DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
          {'data': '__no__', className: 'dt-body-right'},
          {'data': 'name_link', className: 'dt-body-left'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [0, 'desc'],
        'ajax': {
          'url': "{{ route('lg.list') }}",
          'type': 'POST',
          'dataType': 'json',
          'data': d => {
            d.cnt = '{{ $cnt->id }}';
          },
          'error': data => {
            console.log(data.responseJSON.message);
          }
        },
        'pageLength': 25,
        'autoWidth': false
      });
      
      function add() {
        $o.submit.html('Adding...');

				let data = {
					'name': $o.name.val(),
          'country_id': '{{ $cnt->id }}'
				};

				$.ajax({
					'url': "{{ route('lg.store') }}",
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Toast.fire(result.message, '', 'success');

              $o.clear.click();

              datatable.draw(false);
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
              
              if ('errors' in result) {
								display_errors(result.errors, $o, ['name'], 80);
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
          'complete': result => {
            unlock();
            $o.submit.html('Add');
					}
				});
      }

      function update() {
        let id = selectedRow.data().id;

        $o.submit.html('Updating...');

				let data = {
					'name': $o.name.val(),
				};

				$.ajax({
					'url': "{{ route('lg.update', ':id') }}".replace(':id', id),
					'type': 'POST',
					'data': data,
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Toast.fire(result.message, '', 'success');

              unlock();

              $o.clear.click();
              
              datatable.draw(false);
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
              
              if ('errors' in result) {
                display_errors(result.errors, $o, ['name'], 80);
							}

              unlock();
              $o.submit.html('Update');
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);

            unlock();
            $o.submit.html('Update');
					}
				});
      }

      function _delete() {
        let id = selectedRow.data().id;

        Swal.fire({
          'title': 'Deleting...',
          'didOpen': () => {
            Swal.showLoading();
          },
          'allowOutsideClick': false
        });

				$.ajax({
					'url': "{{ route('lg.delete', ':id') }}".replace(':id', id),
					'type': 'POST',
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Toast.fire(result.message, '', 'success');

              datatable.draw(false);
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
              else {
                Swal.close();
              }
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					}
				});
      }

      function lock() {
        $o.submit.prop('disabled', true);
        $o.clear.prop('disabled', true);
        $o.table.find('.edit, .delete').prop('disabled', true);
      }

      function unlock() {
        $o.submit.prop('disabled', false);
        $o.clear.prop('disabled', false);
        $o.table.find('.edit, .delete').prop('disabled', false);
      }
		});
  </script>
@endsection