<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Portfólio e projetos de Marcelo Henrique, desenvolvedor de software e especialista em tecnologia.">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Marcelo Henrique - Desenvolvedor">
        <meta property="og:description" content="Conheça os projetos e inovações em tecnologia desenvolvidos por Marcelo Henrique.">
        <meta property="og:image" content="https://marcelohenriquepro.dev.br/Assets/image/index/logo2_b.png">
        <meta name="keywords" content="tecnologia, inovação, desenvolvimento, programação, negócios, software, Marcelo Henrique, dev, portfólio">


        <title inertia>{{ config('app.name', 'Inertia_Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <!-- Styles -->
        @include('AssetsGlobal/globalCss')
        @include('configApp/analytics')

        <!-- Scripts -->
        @routes
        @vite(['resources/js/config/app.js', "resources/PagesVuejs/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body>
        @inertia
        @include('AssetsGlobal/globalJs')
    </body>
</html>
