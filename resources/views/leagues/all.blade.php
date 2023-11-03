@extends('layouts.app')

@section('page_title', 'All leagues')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">List of all leagues in the system</div>
        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>League</th>
                <th>Country</th>
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
				'table': $('#table'),
			};

      let selectedRow = null;
      let datatable = null;

      datatable = $o.table
      .on('click', '.delete', function(e) {
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
          {'data': 'flag_name', className: 'dt-body-left'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [1, 'asc'],
        'ajax': {
          'url': "{{ route('lg.all_list') }}",
          'type': 'POST',
          'dataType': 'json',
          'data': d => {
          },
          'error': data => {
            console.log(data.responseJSON.message);
          }
        },
        'pageLength': 25,
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
        $o.table.find('.delete').prop('disabled', true);
      }

      function unlock() {
        $o.table.find('.delete').prop('disabled', false);
      }
		});
  </script>
@endsection