@extends('layouts.app')

@section('page_title', 'Players')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">List of countries</div>
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
				'table': $('#table')
			};

      let selectedRow = null;
      let datatable = null;

      datatable = $o.table
      .on('click', '.leagues', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        let data = selectedRow.data();

        location = "{{ route('lg.index', ['cnt' => 'cnt_value']) }}".replace('cnt_value', data.id);
      })
      .on('click', '.empty', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        Swal.fire({
          title: `Do you want to empty ${selectedRow.data().name}?`,
          text: `This action will delete all leagues, all files and all matches associated to ${selectedRow.data().name}`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Empty',
        }).then(result => {
          if (result.isConfirmed) {
            empty();
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
          {'data': 'flag_name', className: 'dt-body-left'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [1, 'asc'],
        'ajax': {
          'url': "{{ route('cnt.list') }}",
          'type': 'POST',
          'dataType': 'json',
          'data': d => {
          },
          'error': data => {
            console.log(data.responseJSON.message);
          }
        },
        'pageLength': -1,
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
        'autoWidth': false
      });
      
      function empty() {
        let id = selectedRow.data().id;

        Swal.fire({
          'title': 'Empting...',
          'didOpen': () => {
            Swal.showLoading();
          },
          'allowOutsideClick': false
        });

				$.ajax({
					'url': "{{ route('cnt.empty', ':id') }}".replace(':id', id),
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
        $o.table.find('.empty').prop('disabled', true);
      }

      function unlock() {
        $o.table.find('.empty').prop('disabled', false);
      }
		});
  </script>
@endsection