<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="robots" content="noindex, nofollow"/>

    <title>Logger UI - {{ config('logger-ui.project') }} - {{ config('logger-ui.environment') }}</title>

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro&display=swap" rel="stylesheet">

    {{
     Illuminate\Support\Facades\Vite::useHotFile(storage_path('vite.hot'))
         ->useBuildDirectory('vendor/logger-ui')
         ->withEntryPoints(['resources/js/app.js', 'resources/css/logger-ui.css'])
    }}
    <link rel="icon" type="image/png" href="{{ asset('vendor/logger-ui/favicon.png') }}"/>
</head>
<body class="h-full">
<div id="logger-ui" class="main bg-base-100 h-full">
    <router-view></router-view>
</div>

@vite('resources/js/app.js')
</body>
</html>
