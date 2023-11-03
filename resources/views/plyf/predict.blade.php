@extends('layouts.app')

@section('page_title', $row->home . ' vs ' . $row->away)

@section('page_styles')
  <style>
    .select2-container--bootstrap-5 {
      display: inline-block;
    }
  </style>
@endsection

@section('page_content')
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">{{$row->home}} vs {{ $row->away }}</h6>
            </div>
            <div class="col-auto d-flex">
              <!-- This button should return to the file selection page -->
              <a href="{{ route('plyf.index') }}" class="btn btn-outline-primary me-2">Change File</a>
              
              <!-- This button should return to the team selection page -->
              <a href="{{ route('plyf.get_select_teams', ['plyf' => $plyf->id]) }}" class="btn btn-outline-primary">Change Teams</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-4">
              <canvas id="winner_canvas" width="400" height="400"></canvas>
            </div>

            <div class="col-4">
              <canvas id="two_five_canvas" width="400" height="400"></canvas>
            </div>

            <div class="col-4">
              <canvas id="bts_canvas" width="400" height="400"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">Home Variables</h6>
            </div>
            <div class="col-auto d-flex">
              {{-- <a class="btn btn-outline-primary" href="{{ route('plyf.index') }}">Change File</a> --}}
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row" id="home_variables">
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
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">Away Variables</h6>
            </div>
            <div class="col-auto d-flex">
              {{-- <a class="btn btn-outline-primary" href="{{ route('plyf.index') }}">Change File</a> --}}
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row" id="away_variables">
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
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">General Variables</h6>
            </div>
            <div class="col-auto d-flex">
              {{-- <a class="btn btn-outline-primary" href="{{ route('plyf.index') }}">Change File</a> --}}
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row" id="general_variables">
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
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row flex-between-center">
            <div class="col-auto">
              <h6 class="mb-0">Match Prediction</h6>
            </div>
            <div class="col-auto d-flex">
              <select class="form-select me-2" id="var_group">
                <option value="" disabled data-vars="[]">Custom</option>
                @foreach ($var_groups as $vg)
                  <option value="{{ $vg->id }}" data-vars="{{ json_encode($vg->vars ?? []) }}"
                      {{ $loop->first ? 'selected' : '' }}>{{ $vg->name }}</option>
                @endforeach
              </select>
              <!-- This button should start making research -->
              <button class="btn btn-dark" id="btn_search">Search</button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <input type="hidden" id="row" value="{{ json_encode($row) }}">
          <input type="hidden" id="vars" value="{{ json_encode($vars) }}">

          <!-- Here is the prediction table containing all variables -->
          <table class="table table-bordered" id="prediction_table">
            <thead>
              <tr>
                <th>Home</th>
                <th>Away</th>
                <th>Full-Time</th>
                <th>Date</th>
                
                @for($i = 1; $i <= 20; $i++)
                  <?php $id = "x$i"; ?>
                  <th title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</th>
                @endfor

                @for($i = 1; $i <= 20; $i++)
                  <?php $id = "y$i"; ?>
                  <th title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</th>
                @endfor

                @for($i = 1; $i <= 10; $i++)
                  <?php $id = "z$i"; ?>
                  <th title="{{$vars[$id]->name}}" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover">{{$vars[$id]->code}}</th>
                @endfor
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

@section('third_party_js')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
  <script>
    Chart.register(ChartDataLabels);
  </script>
@endsection

@section('page_scripts')
  @parent
  <script type="text/javascript">
    $(() => {
			let $o = {
				'home_variables': $('#home_variables'),
				'away_variables': $('#away_variables'),
				'general_variables': $('#general_variables'),
				'row': $('#row'),
				'vars': $('#vars'),
				'var_group': $('#var_group'),
				'prediction_table': $('#prediction_table'),
				'btn_search': $('#btn_search'),
			};

      let winner_chart = null;
      let two_five_chart = null;
      let bts_chart = null;

      let prediction_datatable = null;
      let row = JSON.parse($o.row.val());
      let vars = JSON.parse($o.vars.val());

      $o.var_group.change(function() {
        let vars = $(this).find('option:selected').data('vars');

        sync_columns($('.var:checked').prop('checked', false));

        vars.forEach(v => {
          sync_columns($('#'+v).prop('checked', true));
        });
      });

      $o.btn_search.click(function(e) {
				e.preventDefault();

        $o.btn_search.html('Searching...');

        let vars = {};

        $('.var:checked').each(function() {
          vars[this.id] = row[this.id];
        });

        $.ajax({
          'url': "{{ route('plyf.search') }}",
          'type': 'POST',
          'data': {vars},
          'dataType': 'json',
					'success': result => {
						if (result.status == 'success') {
              Toast.fire(result.message, '', 'success');
              
              prediction_datatable.clear().row.add(row).rows.add(result.matches).draw();

              // Winner Chart

              let home_win = 0;
              let away_win = 0;
              let draw = 0;

              let over_2_5 = 0;
              let under_2_5 = 0;

              let bts_yes = 0;
              let bts_no = 0;

              result.matches.forEach(match => {
                // Winner

                if(match.winner === match.home) {
                  home_win++;
                }
                else if(match.winner === match.away) {
                  away_win++;
                }
                else {
                  draw++;
                }

                // 2.5

                let goals = match.home_goals + match.away_goals;

                if(goals > 2.5) {
                  over_2_5++;
                }
                else {
                  under_2_5++;
                }

                // BTS

                if(match.home_goals > 0 && match.away_goals > 0) {
                  bts_yes++;
                }
                else {
                  bts_no++;
                }
              });

              winner_chart.config.data.datasets[0].data = [home_win, away_win, draw];
              winner_chart.update();

              two_five_chart.config.data.datasets[0].data = [over_2_5, under_2_5];
              two_five_chart.update();

              bts_chart.config.data.datasets[0].data = [bts_yes, bts_no];
              bts_chart.update();
						} else {
							if (result.message) {
                Toast.fire('Error!', result.message, 'error');
							}
						}
					},
					'error': result => {
						console.log(result.responseJSON.message);
					},
          'complete': result => {
            $o.btn_search.html('Search');
					}
				});
			});

      // Create the datatable
      function create_datatable() {
         let columns = [
          {
            data: 'home',
          },
          {
            data: 'away',
          },
          {
            data: 'score',
          },
          {
            data: 'date',
            render: (data, type, row, meta) => {
              if(data == '-') {
                return data;
              }

              if (type === 'display' || type === 'filter') {
                return moment(data).format('DD/MM/YYYY');
              }

              return data;
            },
          }
        ];

        // Give id to variables and hide them

        for(let id in vars) {
          columns.push({
            name: id,
            data: id,
            render: (data, type, row, meta) => {
              if (data === null) {
                return 'N/A';
              }

              if (type === 'display' || type === 'filter') {
                return parseFloat(data).toFixed(vars[id].dec_place);
              }

              return data;
            },
            visible: false,
            searchable: false,
          });
        }

        prediction_datatable = $o.prediction_table.DataTable({
          columns,
          paging: false,
          searching: false,
          // info: false,
          // ordering: false,
          autoWidth: false,
          createdRow: function (row, data, dataIndex) {
            if (data.score != '-') {
              if(data.winner == data.home) {
                $(row).css({
                  'background-color': '#00ff00',
                });
              }
              else if(data.winner == data.away) {
                $(row).css({
                  'background-color': '#ff0000',
                  'color': '#ffffff'
                });
              }
              else {
                $(row).css({
                  'background-color': '#ffff00',
                });
              }
            }
          },
        });

        // Wrap the table to let it scroll horizontally
        $o.prediction_table.wrap('<div style="overflow-x: auto;"/>');

        prediction_datatable.clear().row.add(row).draw();
      }

      // Link variables to the datatables columns
      function sync_columns($elements) {
        $elements.each(function() {
          prediction_datatable.column(`${this.id}:name`).visible($(this).prop('checked'));
        });
      }

      function sync_groups() {
        let var_checked = JSON.stringify($('.var:checked').map(function() {return this.id}).get().sort());

        let options = $o.var_group.children().get();
        let found = 0;

        for(let i = 1; i < options.length; i++) {
          let group_vars = JSON.stringify($(options[i]).data('vars').sort());

          if(var_checked === group_vars) {
            found = i;
            break;
          }
        }

        $(options[found]).prop('selected', true);
      }

      // Init charts
      function init_charts() {

        function options(label) {
          return {
            hover: {mode: null},
            plugins: {
              title: {
                display: true,
                text: label
              },
              tooltip: {
                enabled: false
              },
              datalabels: {
                color: '#fff',
                backgroundColor: 'rgba(127, 127, 127, .5)',
                borderRadius: 15,
                labels: {
                  title: {
                    font: {
                      weight: 'bold',
                      size: 25
                    }
                  },
                },
                formatter: (value, ctx) => {
                  let data = ctx.chart.data.datasets[0].data;
                  let sum = data.reduce((a, b) => a + b);
                  let per_diff = 100;

                  let mapped = data.map((d, ind) => {
                    let per = parseInt(d * 100 / sum);

                    per_diff -= per;

                    return {ind, per};
                  });

                  if(per_diff == 0) {
                    return mapped[ctx.dataIndex].per + '%';
                  }

                  mapped.sort((a, b) => {
                    if(a.per < b.per) {
                      return -1;
                    }
                    else if(a.per > b.per) {
                      return 1;
                    }

                    return 0;
                  });

                  for(let i = 0; i < per_diff; mapped[i].per++, i++);

                  return mapped.find(a => a.ind == ctx.dataIndex).per + '%';
                },
              }
            }
          };
        }

        // Init winner chart
   
        const winner_canvas = document.getElementById('winner_canvas').getContext('2d');
        
        winner_chart = new Chart(winner_canvas, {
          type: 'pie',
          data: {
            labels: [
              'Home',
              'Away',
              'Draw'
            ],
            datasets: [{
              label: 'My First Dataset',
              data: [0, 0, 0],
              backgroundColor: [
                'rgb(0, 255, 0)',
                'rgb(255, 0, 0)',
                'rgb(255, 255, 0)',
              ],
              hoverOffset: 4
            }]
          },
          options: options('Winner')
        });

        // Init two_five chart

        const two_five_canvas = document.getElementById('two_five_canvas').getContext('2d');
        
        two_five_chart = new Chart(two_five_canvas, {
          type: 'pie',
          data: {
            labels: [
              'Over',
              'Under',
            ],
            datasets: [{
              label: 'My First Dataset',
              data: [0, 0],
              backgroundColor: [
                'rgb(0, 0, 255)',
                'rgb(255, 0, 0)',
              ],
              hoverOffset: 4
            }]
          },
          options: options('2.5')
        });

        // Init bts chart

        const bts_canvas = document.getElementById('bts_canvas').getContext('2d');
        
        bts_chart = new Chart(bts_canvas, {
          type: 'pie',
          data: {
            labels: [
              'Yes',
              'No',
            ],
            datasets: [{
              label: 'My First Dataset',
              data: [0, 0],
              backgroundColor: [
                'rgb(0, 0, 255)',
                'rgb(255, 0, 0)',
              ],
              hoverOffset: 4
            }]
          },
          options: options('BTS')
        });
      }

      create_datatable();

      $('.var').click(function() {
        sync_columns($(this));
        sync_groups();
      });

      init_charts(); // Initialize the charts

      $o.var_group.change();

      $o.btn_search.click();
		});
  </script>
@endsection