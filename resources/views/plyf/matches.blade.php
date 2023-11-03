@extends('layouts.app')

@section('page_title', 'Matches')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">Matches of the file "{{ $plyf->name }}"</h6>
            </div>
            <div class="col-auto d-flex">
              <a class="btn btn-outline-primary" href="{{ route('plyf.get_select_teams', ['plyf' => $plyf->id]) }}">Select teams</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>Year</th>
                <th>Time</th>
                <th>Home</th>
                <th>Away</th>
                <th>Score</th>
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
      .DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
          {'data': '__no__', className: 'dt-body-right'},
          {'data': 'year', className: 'dt-body-right'},
          {'data': 'time', className: 'dt-body-right'},
          {'data': 'home', className: 'dt-body-left'},
          {'data': 'away', className: 'dt-body-left'},
          {'data': 'score', className: 'dt-body-right'},
        ],
        'order': [0, 'asc'],
        'ajax': {
          'url': "{{ route('plyf.matches_list') }}",
          'type': 'POST',
          'dataType': 'json',
          'data': d => {
            d.plyf = '{{ $plyf->id }}';
          },
          'error': data => {
            console.log(data.responseJSON.message);
          }
        },
        'pageLength': -1,
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
        'autoWidth': false
      });
      
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