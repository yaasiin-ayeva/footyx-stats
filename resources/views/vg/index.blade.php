@extends('layouts.app')

@section('page_title', 'Variables Groups')

@section('page_content')
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">Add a group of variables</div>
        <div class="card-body">
          <form id="form" class="row gy-3" novalidate>
            <fieldset class="col-12">
              <label class="form-label">Name of the group</label>
              <input class="form-control" type="text" id="name" maxlength="255" placeholder="Enter the name of the group">
              <div class="invalid-feedback"></div>
            </fieldset>

            <fieldset class="col-12">
              <label class="form-label">Select Home Variables</label>
              <div id="home_vars" class="row">
                @for($i = 1; $i <= 20; $i++)
                  <?php $id = "x$i"; ?>

                  <div style="width: 20%;">
                    <div class="form-check">
                      <input class="form-check-input var" type="checkbox" value="" id="{{$id}}">
                      <label class="form-check-label" for="{{$id}}" title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</label>
                    </div>
                  </div>
                @endfor
              </div>
            </fieldset>

            <fieldset class="col-12">
              <label class="form-label">Select Away Variables</label>
              <div id="away_vars" class="row">
                @for($i = 1; $i <= 20; $i++)
                  <?php $id = "y$i"; ?>

                  <div style="width: 20%;">
                    <div class="form-check">
                      <input class="form-check-input var" type="checkbox" value="" id="{{$id}}">
                      <label class="form-check-label" for="{{$id}}" title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</label>
                    </div>
                  </div>
                @endfor
              </div>
            </fieldset>

            <fieldset class="col-12">
              <label class="form-label">Select General Variables</label>
              <div id="general_vars" class="row">
                @for($i = 1; $i <= 10; $i++)
                  <?php $id = "z$i"; ?>

                  <div style="width: 20%;">
                    <div class="form-check">
                      <input class="form-check-input var" type="checkbox" value="" id="{{$id}}">
                      <label class="form-check-label" for="{{$id}}" title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</label>
                    </div>
                  </div>
                @endfor
              </div>
            </fieldset>

            <div class="col-12 text-center">
              <button class="btn btn-primary" type="button" id="submit" data-type="add">Add</button>
              <button class="btn btn-outline-primary" type="button" id="clear">Clear</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">List of existing groups</div>
        <div class="card-body">
          <table id="table" class="table table-bordered text-center nowrap w-100">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Variables</th>
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
        'form': $('#form'),
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
        $('.var').prop('checked', false);
        
        $o.submit.data('type', 'add').html('Add');
      });

      datatable = $o.table
      .on('click', '.edit', function(e) {
        selectedRow = datatable.row($(this).closest('tr'));

        let data = selectedRow.data();

        $o.name.removeClass('is-valid is-invalid').val(data.name);

        $('.var').prop('checked', false);
        data.vars.forEach(v => {
          $('#'+v).prop('checked', true);
        });

        $o.submit.data('type', 'update').html('Update');

        let top = $o.form.offset().top - 80;
        window.scrollTo({top, behavior: 'smooth'});
      })
      .on('click', '.dissolve', function(e) {
        if($o.submit.data('type') == 'update') {
          $o.clear.click();
        }

        selectedRow = datatable.row($(this).closest('tr'));

        Swal.fire({
          title: `Do you want to dissolve ${selectedRow.data().name}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Dissolve',
        }).then(result => {
          if (result.isConfirmed) {
            dissolve();
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
          {'data': 'name', className: 'dt-body-left'},
          {'data': 'var_count', className: 'dt-body-right'},
          {'data': 'actions', 'orderable': false}
        ],
        'order': [0, 'desc'],
        'ajax': {
          'url': "{{ route('vg.list') }}",
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
      
      function add() {
        $o.submit.html('Adding...');

				let data = {
					'name': $o.name.val(),
          'vars': $('.var:checked').map(function() {
            return this.id;
          }).get()
				};

				$.ajax({
					'url': "{{ route('vg.store') }}",
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
          'vars': $('.var:checked').map(function() {
            return this.id;
          }).get()
				};

				$.ajax({
					'url': "{{ route('vg.update', ':id') }}".replace(':id', id),
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

      function dissolve() {
        let id = selectedRow.data().id;

        Swal.fire({
          'title': 'Dissolving...',
          'didOpen': () => {
            Swal.showLoading();
          },
          'allowOutsideClick': false
        });

				$.ajax({
					'url': "{{ route('vg.dissolve', ':id') }}".replace(':id', id),
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
        $o.table.find('.edit, .dissolve').prop('disabled', true);
      }

      function unlock() {
        $o.submit.prop('disabled', false);
        $o.clear.prop('disabled', false);
        $o.table.find('.edit, .dissolve').prop('disabled', false);
      }
		});
  </script>
@endsection