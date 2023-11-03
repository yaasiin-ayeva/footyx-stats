@extends('layouts.app')

@section('page_title', 'Matches')

@section('page_content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">Schedules</h6>
            </div>
            <div class="col-auto d-flex">
              <a class="btn btn-danger" href="fdsfds">Delete selection</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th><input class="form-check-input" type="checkbox"></th>
                <th>Home</th>
                <th>Away</th>
                <th>Group 1</th>
                <th>Group 2</th>
                <th>Group 3</th>
                <th>Group 4</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Brighton</td>
                <td>Chelsea</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Newcastle</td>
                <td>Everton</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Liverpool</td>
                <td>Arsenal</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Manchester Utd</td>
                <td>Tottenham</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Arsenal</td>
                <td>Crystal Palace</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>West Ham</td>
                <td>Manchester City</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>

              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td>Cardiff</td>
                <td>Wolves</td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 15%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 40%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td style="width: 150px;">
                  <div class="d-flex">
                    <div style="width: 25%; height: 20px; background-color: rgb(0,255,0);" class="fw-bold text-white"></div>
                    <div style="width: 45%; height: 20px; background-color: rgb(255,255,0);"></div>
                    <div style="width: 30%; height: 20px; background-color: rgb(255,0,0);"></div>
                  </div>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-sync me-1"></i> Refresh</button></td>
              </tr>
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
        // 'processing': true,
        // 'serverSide': true,
        'columns': [
          {orderable: false},
          null,
          null,
          null,
          null,
          null,
          null,
          null,
        ],
        // 'order': [0, 'asc'],
        // 'ajax': {
        //   'url': "{{ route('plyf.matches_list') }}",
        //   'type': 'POST',
        //   'dataType': 'json',
        //   'data': d => {
        //     d.plyf = '{{}}';
        //   },
        //   'error': data => {
        //     console.log(data.responseJSON.message);
        //   }
        // },
        // 'pageLength': -1,
        // 'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
        'ordering': false,
        'autoWidth': false,
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