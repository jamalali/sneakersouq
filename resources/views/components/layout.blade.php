<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

    <link href="/assets/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="/assets/fontawesome/css/brands.css" rel="stylesheet">
    <link href="/assets/fontawesome/css/solid.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <x-navbar />

    <!-- Page Heading -->
    @if (isset($header))
      <div class="border-b py-5">
        <div class="container">
          <h1 class="title is-5">
            {{ $header }}
          </h1>
        </div>
      </div>
    @endif
    
    <section class="section has-background-light">
      <div class="container box">
        {{ $slot }}
      </div>
    </section>
  </body>
</html>