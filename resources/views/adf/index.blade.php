@extends('layouts.app')

@section('page_title', 'League files')

@section('page_content')
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">Load files for the league "{{ $lg->name }}"</div>
        <div class="card-body">
          <form id="form" class="row gy-3" novalidate>
            <div class="col-12">
              <label for="files" class="form-label">Select the files</label>
              <input class="form-control" type="file" id="files" accept=".xlsx" multiple>
              <div class="invalid-feedback"></div>
            </div>

            <div class="col-12 text-center">
              <button class="btn btn-primary" type="submit" id="submit">Load</button>
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
        <div class="card-header">List of files for the league "{{ $lg->name }}"</div>
        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>Name of file</th>
                <th>Loaded by</th>
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
				'files': $('#files'),
				'submit': $('#submit'),
				'clear': $('#clear'),

				'table': $('#table'),
			};

      let selectedRow = null;
      let datatable = null;

      $o.submit.click(function(e) {
				e.preventDefault();

        lock();

        $o.submit.html('Loading...');

        let fd = new FormData();

        let files = $o.files.get(0).files;

        for(let c = files.length, i = 0; i < c; i++) {
          fd.append('files[]', files[i]);
        }

        fd.append('league_id', '{{ $lg->id }}');

        $.ajax({
          'url': "{{ route('adf.load') }}",
          'type': 'POST',
          'data': fd,
          'contentType': false,
          'processData': false,
          'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Swal.fire(result.message, '', 'success');

              $o.clear.click();

              datatable.draw(false);
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
              
              if ('errors' in result) {
								display_errors(result.errors, $o, ['files'], 80);
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
          'complete': result => {
            unlock();
            $o.submit.html('Load');
					}
				});
			});

      $o.clear.click(function(e) {
        e.preventDefault();

        $o.files.val('').removeClass('is-valid is-invalid');
      });

      datatable = $o.table
      .on('click', '.delete', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        Swal.fire({
          title: `Do you want to delete the file ${selectedRow.data().name}?`,
          text: `This action will also delete all matches in the file`,
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
          {'data': 'loader', className: 'dt-body-left'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [0, 'desc'],
        'ajax': {
          'url': "{{ route('adf.list') }}",
          'type': 'POST',
          'dataType': 'json',
          'data': d => {
            d.lg = '{{ $lg->id }}';
          },
          'error': data => {
            console.log(data.responseJSON.message);
          }
        },
        'pageLength': -1,
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
        'autoWidth': false
      });
      
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
					'url': "{{ route('adf.delete', ':id') }}".replace(':id', id),
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
        $o.table.find('.delete').prop('disabled', true);
      }

      function unlock() {
        $o.submit.prop('disabled', false);
        $o.clear.prop('disabled', false);
        $o.table.find('.delete').prop('disabled', false);
      }
		});
  </script>
@endsection