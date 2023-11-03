@extends('layouts.app')

@section('page_title', 'Players')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">List of players</div>
        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
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
				'table': $('#table')
			};

      let selectedRow = null;
      let datatable = null;

      datatable = $o.table
      .on('click', '.delete', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        Swal.fire({
          title: `Do you want to delete ${selectedRow.data().email}?`,
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
          {'data': 'image', className: 'dt-body-left'},
          {'data': 'email', className: 'dt-body-left'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [0, 'desc'],
        'ajax': {
          'url': "{{ route('ply.list') }}",
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
					'url': "{{ route('ply.delete', ':id') }}".replace(':id', id),
					'type': 'POST',
					'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Toast.fire({
                'title': result.message,
                'icon': 'success'
              });

              datatable.draw(false);
						} else {
							if (result.message) {
                Toast.fire({
                  title: 'Error!',
                  text: result.message,
                  icon: 'error'
                });
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