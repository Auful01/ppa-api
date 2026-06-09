<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('../assets/img/apple-icon.png') }}" />
  <link rel="icon" type="image/png" href="{{ asset('../assets/img/favicon.png') }}" />
  <title>Forbidden Page</title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('../assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('../assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Popper -->
  <script src="https://unpkg.com/@popperjs/core@2"></script>
  <!-- Main Styling -->
  <link href="{{ asset('../assets/css/argon-dashboard-tailwind.css?v=1.0.0') }}" rel="stylesheet" />
</head>

<body
  class="m-0 font-sans text-base antialiased font-normal text-left bg-white leading-default dark:bg-slate-900 text-slate-500 dark:text-white/80">
  <main class="mt-0 transition-all duration-200 ease-in-out">
    <div class="pb-0 pt-0 h-full min-h-screen items-start p-0 relative overflow-hidden flex bg-cover bg-center"
      style="background-image: url('{{ asset('../assets/img/illustrations/404.svg') }}');">
      <div class="container">
        <div class="flex flex-wrap items-center justify-center -mx-3 mt-42 lg:mt-75">
          <div class="w-full max-w-full px-6 mx-auto text-center shrink-0 md:flex-0 md:w-7/12 lg:w-6/12">
            <h1 class="font-bold text-blue-500 text-['calc(1.625rem_+_4.5vw)'] leading-tighter xl:text-20">
              @yield('code')</h1>
            <h2>@yield('title')</h2>
            <p class="text-xl font-normal leading-relaxed">@yield('message')</p>
            <a href="{{ url('/') }}"
              class="inline-block px-5 py-2.5 mt-6 mb-2 text-sm font-bold text-center text-white align-middle transition-all ease-in border-0 rounded-lg shadow-md cursor-pointer active:-translate-y-px active:hover:text-white hover:-translate-y-px hover:shadow-xs leading-normal tracking-tight-rem bg-150 bg-x-25 bg-gradient-to-tl from-zinc-800 to-zinc-700 hover:text-white">Go
              to Homepage</a>
          </div>
        </div>
      </div>
    </div>

  </main>

</body>

<script src="{{ asset('../assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<!-- main script file  -->
<script src="{{ asset('../assets/js/argon-dashboard-pro-tailwind.js?v=1.0.0') }}"></script>

</html>