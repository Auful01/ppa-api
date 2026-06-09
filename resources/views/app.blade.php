<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('../assets/img/POLICE_LOGO2.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('../assets/img/POLICE_LOGO2.png') }}" />

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!--     Fonts and icons     -->
    <link href="{{ asset('../assets/css/font.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="{{ asset('../assets/js/fontAwesome27c970a153.js') }}" crossorigin="anonymous"></script>
    <!-- Nucleo Icons -->
    <link href="{{ asset('../assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('../assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Popper -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Main Styling -->
    <link href="{{ asset('../assets/css/argon-dashboard-tailwind.css?v=1.0.1') }}" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <style>
        .dark {

            body {
                background: #051139 !important;
            }
        }
    </style>
</head>

@auth

    <body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-white text-slate-500">
        <!-- class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-white text-slate-500" style="background: url(http://127.0.0.1:8000/bg.jpg) ;
  background-size: cover;
  background-blend-mode: multiply;"> -->
    @else

        <body class="m-0 font-sans antialiased font-normal bg-white text-start text-base leading-default text-slate-900">
        @endauth
        @inertia
    </body>

    <!-- plugin for charts  -->
    <script src="{{ asset('./assets/js/plugins/chartjs.min.js') }}" async></script>

    <!-- plugin for scrollbar  -->
    <script src="{{ asset('../assets/js/plugins/perfect-scrollbar.min.js') }}" async></script>
    <!-- main script file  -->
    <script src="{{ asset('../assets/js/argon-dashboard-pro-tailwind.js') }}" async></script>
    <script src="{{ asset('../assets/js/argon-dashboard-pro-tailwind.min.js?v=1.0.1') }}" async></script>

</html>
