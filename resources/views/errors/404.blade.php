@extends('layouts.auth')

@section('page_title', 'Error 404')

@section('page_content')
  <div class="text-center">
    <h1 class="text-error">4<i class="mdi mdi-emoticon-sad"></i>4</h1>
    <h4 class="text-uppercase text-danger mt-3">Page Not Found</h4>
    <p class="text-muted mt-3">It's looking like you may have taken a wrong turn. Don't worry... it
      happens to the best of us. Here's a
      little tip that might help you get back on track.</p>

    <a class="btn btn-info mt-3" href="/"><i class="mdi mdi-reply"></i> Return Home</a>
  </div>
@endsection