@extends('layouts.auth')

@section('page_title', 'Error 405')

@section('page_content')
  <div class="text-center">
    <h1 class="text-error">4<i class="mdi mdi-emoticon-confused"></i>5</h1>
    <h4 class="text-uppercase text-danger mt-3">Method Not Allowed</h4>
    <p class="text-muted mt-3">The server knows the request method, but the target resource doesn't support this method.</p>

    <a class="btn btn-info mt-3" href="/"><i class="mdi mdi-reply"></i> Return Home</a>
  </div>
@endsection