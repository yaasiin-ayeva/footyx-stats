<nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
  <script>
    var navbarStyle = localStorage.getItem("navbarStyle");
    if (navbarStyle && navbarStyle !== 'transparent') {
      document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
    }
  </script>
  <div class="d-flex align-items-center">
    <div class="toggle-icon-wrapper">

      <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>

    </div><a class="navbar-brand" href="{{ route('index') }}">
      <div class="d-flex align-items-center py-3"><img class="me-2" src="/assets/img/logo-100.png" alt="" width="40" /><span class="font-sans-serif">{{ config('app.name') }}</span>
      </div>
    </a>
  </div>
  <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
    <div class="navbar-vertical-content scrollbar">
      <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
        <li class="nav-item">
          <!-- label-->
          {{-- <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
            <div class="col-auto navbar-vertical-label">App
            </div>
            <div class="col ps-0">
              <hr class="mb-0 navbar-vertical-divider" />
            </div>
          </div> --}}
          <!-- parent pages--><a class="nav-link" href="{{ route('dashboard') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-chart-pie"></span></span><span class="nav-link-text ps-1">Dashboard</span>
            </div>
          </a>
        </li>

        <li class="nav-item">
          <!-- label-->
          <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
            <div class="col-auto navbar-vertical-label">App
            </div>
            <div class="col ps-0">
              <hr class="mb-0 navbar-vertical-divider" />
            </div>
          </div>
          
          <a class="nav-link" href="{{ route('ply.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-user"></span></span><span class="nav-link-text ps-1">Players</span>
            </div>
          </a>
          
          <a class="nav-link" href="{{ route('cnt.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-flag"></span></span><span class="nav-link-text ps-1">Countries</span>
            </div>
          </a>
          
          <a class="nav-link" href="{{ route('lg.all') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-trophy"></span></span><span class="nav-link-text ps-1">Leagues</span>
            </div>
          </a>
          
          <a class="nav-link" href="{{ route('adf.all') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-folder-open"></span></span><span class="nav-link-text ps-1">Files</span>
            </div>
          </a>

          <a class="nav-link" href="{{ route('vg.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-object-group"></span></span><span class="nav-link-text ps-1">Groups</span>
            </div>
          </a>
        </li>

        <?php
          use App\Models\Country;
          use App\Models\League;

          $countries = Country::orderBy('name')->get();
          $leagues = League::orderBy('name')->get();

          $leagues_indexed = [];

          foreach ($leagues as $league) {
            $leagues_indexed[$league->country_id][] = $league;
          }
        ?>

        <li class="nav-item">
          <!-- label-->
          <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
            <div class="col-auto navbar-vertical-label">Archive
            </div>
            <div class="col ps-0">
              <hr class="mb-0 navbar-vertical-divider" />
            </div>
          </div>

          @foreach ($countries as $cnt)
            <a class="nav-link dropdown-indicator" href="#cnt{{ $cnt->id }}" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="authentication">
              <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                  <span>
                    <img style="display: inline;"
                      src="https://flagcdn.com/16x12/{{$cnt->code}}.png"
                      srcset="https://flagcdn.com/32x24/{{$cnt->code}}.png 2x,
                        https://flagcdn.com/48x36/{{$cnt->code}}.png 3x"
                      width="16"
                      height="12"
                      alt="{{$cnt->code}}">
                  </span>  
                </span>
                <span class="nav-link-text ps-1">{{ $cnt->name }}</span>
              </div>
            </a>

            <ul class="nav collapse" id="cnt{{ $cnt->id }}">
              @foreach (($leagues_indexed[$cnt->id] ?? []) as $lg)
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('adf.index', ['lg' => $lg->id]) }}" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ $lg->name }}</span>
                    </div>
                  </a>
                </li>
              @endforeach
              <li class="nav-item">
                <a class="nav-link" href="{{ route('lg.index', ['cnt' => $cnt->id]) }}" data-bs-toggle="" aria-expanded="false">
                  <div class="d-flex align-items-center"><span class="nav-link-text ps-1"><i class="fas fa-plus"></i> Add league</span>
                  </div>
                </a>
              </li>
            </ul>
          @endforeach
        </li>
      </ul>
    </div>
  </div>
</nav>