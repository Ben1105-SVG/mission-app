<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Mission Application') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-black antialiased">
        <noscript>
            <div class="flex min-h-screen items-center justify-center bg-red-50 p-6 text-center text-red-700">
                This experience requires JavaScript to load the questionnaire interface.
            </div>
        </noscript>
        <div id="app"></div>
    </body>
</html>

        