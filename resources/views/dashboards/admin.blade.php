@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('page_styles')
  <style>
    .card-body.position-relative {
      min-height: 130px;
    }
  </style>
@endsection

@section('page_content')
  <div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-1.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Players{{--<span class="badge badge-soft-warning rounded-pill ms-2">-0.23%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":{{ $players }}}'>0</div><a class="fw-semi-bold fs--1 text-nowrap" href="{{ route('ply.index') }}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-2.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Countries{{--<span class="badge badge-soft-info rounded-pill ms-2">0.0%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":{{ $countries }}}'>0</div><a class="fw-semi-bold fs--1 text-nowrap" href="{{ route('cnt.index') }}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-3.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Leagues{{--<span class="badge badge-soft-success rounded-pill ms-2">9.54%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif text-success" data-countup='{"endValue":{{ $leagues }}}'>0</div>
          <a class="fw-semi-bold fs--1 text-nowrap" href="{{ route('lg.all') }}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Files{{--<span class="badge badge-soft-warning rounded-pill ms-2">-0.23%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif text-success" data-countup='{"endValue":{{ $files }}}'>0</div>
          <a class="fw-semi-bold fs--1 text-nowrap" href="{{ route('adf.all') }}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-5.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Matches{{--<span class="badge badge-soft-info rounded-pill ms-2">0.0%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif" data-countup='{"endValue":{{ $matches }}}'>0</div>
          {{-- <a class="fw-semi-bold fs--1 text-nowrap" href="/app/e-commerce/orders/order-list.html">All orders<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a> --}}
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card overflow-hidden" style="min-width: 12rem">
        <div class="bg-holder bg-card" style="background-image:url(/assets/img/icons/spot-illustrations/corner-1.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
          <h6>Groups{{--<span class="badge badge-soft-warning rounded-pill ms-2">-0.23%</span>--}}</h6>
          <div class="display-4 fs-4 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":{{ $vg }}}'>0</div>
          <a class="fw-semi-bold fs--1 text-nowrap" href="{{ route('vg.index') }}">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('third_party_js')
  <script src="/vendors/countup/countUp.umd.js"></script>
@endsection

@section('page_scripts')
  @parent

@endsection