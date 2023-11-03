@extends('layouts.app')

@section('page_title', 'Select Teams')

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
              <h6 class="mb-0">Select match to predict from "{{ $plyf->name }}"</h6>
            </div>
            <div class="col-auto d-flex">
              <a class="btn btn-outline-primary" href="{{ route('plyf.index') }}">Change File</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <input type="hidden" id="teams" value='{{ json_encode($teams) }}'>

          <form id="form" method="GET" action="{{ route('plyf.get_predict') }}" class="row gy-3" novalidate>
            <div class="d-flex">

              <div class="w-75">
                <!-- This select box should contain all teams -->
                <select class="form-select d-inline-block" style="width: 30%;" name="home" id="home">
                </select>

                <h4 class="d-inline-block mx-3">VS</h4>

                <!-- This select box should also contain all teams -->
                <select class="form-select d-inline-block" style="width: 30%;" name="away" id="away">
                </select>

                <input type="hidden" name="plyf" value='{{ $plyf->id }}'>
              </div>

              <!-- This button performs the analysis -->
              <button class="btn btn-primary ms-auto" type="submit">Analyse</button>
            </div>
          </form>
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
				'teams': $('#teams'),
				'home': $('#home'),
				'away': $('#away'),
				'submit': $('#submit'),
			};

      let teams = JSON.parse($o.teams.val());

      // Populate a team selection box without a team
      function populate_without($element, teams, without = '') {
        let oldvalue = $element.val();

        $element.children().remove();

        teams.forEach(t => {
          if(t.name != without) {
            $element.append(`<option value="${t.name}" ${t.name == oldvalue ? 'selected' : ''}>${t.name}</option>`);
          }
        });

        $element.select2({
          theme: 'bootstrap-5',
        });
      }

      populate_without($o.home, teams, teams[1].name);
      populate_without($o.away, teams, teams[0].name);

      // When the user selects the "Home Team"
      $o.home.change(function() {
        populate_without($o.away, teams, $(this).val());
      });

      // When the user selects the "Away Team"
      $o.away.change(function() {
        populate_without($o.home, teams, $(this).val());
      });

      // $o.submit.click(function(e) {
			// 	e.preventDefault();

      //   lock();

      //   $o.submit.html('Loading...');

      //   let fd = new FormData();

      //   let files = $o.file.get(0).files;

      //   if(files.length) {
      //     fd.append('file', files[0]);
      //   }

      //   $.ajax({
      //     'url': "{{ route('plyf.load') }}",
      //     'type': 'POST',
      //     'data': fd,
      //     'contentType': false,
      //     'processData': false,
      //     'dataType': 'json',
			// 		'success': result => {
			// 			if (result.status == 'success') {
      //         Toast.fire(result.message, '', 'success');

      //         $o.clear.click();

      //         datatable.draw(false);
			// 			} else {
			// 				if (result.message) {
      //           Toast.fire('Error!', result.message, 'error');
			// 				}
              
      //         if ('errors' in result) {
			// 					display_errors(result.errors, $o, ['file'], 80);
			// 				}
			// 			}
			// 		},
			// 		'error': result => {
			// 			console.log(result.responseJSON.message);
			// 		},
      //     'complete': result => {
      //       unlock();
      //       $o.submit.html('Load');
			// 		}
			// 	});
			// });
		});
  </script>
@endsection