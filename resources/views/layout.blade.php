<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Meta Information -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="robots" content="noindex, nofollow" />

        <!-- Title -->
        <title>Logger UI - {{ config('logger-ui.project') }} - {{ config('logger-ui.environment') }}</title>

        <!-- Style sheets -->
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro&display=swap" rel="stylesheet">

        <link href="{{ asset(mix('app.css', 'vendor/logger-ui')) }}" rel="stylesheet" type="text/css" />
    </head>
    <body class="bg-gray-900">
        <div id="logger-ui" class="main">
            <router-view></router-view>
        </div>

        <script src="{{ asset(mix('app.js', 'vendor/logger-ui')) }}"></script>
    </body>
</html>
