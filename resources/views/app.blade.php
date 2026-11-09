<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        <title inertia>BeeNote</title>

        <meta name="theme-color" content="#17382C">
        <!-- Pas de thème sombre : on empêche le navigateur d'assombrir de force la palette. -->
        <meta name="color-scheme" content="only light">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <link rel="manifest" href="/manifest.webmanifest">
        <!-- Scripts -->
        @routes(nonce: \Illuminate\Support\Facades\Vite::cspNonce())
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
