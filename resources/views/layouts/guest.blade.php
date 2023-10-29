<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  </head>
  <body>
    
    <div class="hero is-fullheight has-background-light">
      <div class="hero-body is-justify-content-center">
        <div style="width:400px;">
          <x-application-logo class="mb-4 image mx-auto" width="100" />
          <div class="box">
            {{ $slot }}
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
