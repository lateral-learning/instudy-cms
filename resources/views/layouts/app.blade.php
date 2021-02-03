<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'inStudy CMS') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">


    <style>
        .linkUpdate::after {
            content: " (Clicca qui per modificare) "
        }

        .warner.onupdate {
            display: none;
        }

        .mainTitle.updateElement~form .warner.onupdate {
            display: inline;
        }
    </style>

    <script>
        function toDataAttributes(obj) {
            return Object.entries(obj).map(([k, v]) => `data-${k}='${v}'`).join(" ");
        }

        document.addEventListener("click", function(evt) {
            const link = evt.target.closest(".linkUpdate");
            if (link) {
                const dataset = Object.entries(link.dataset);
                const id = dataset[0][1];
                document.querySelector(".mainTitle").innerText = "Aggiorna l'elemento " + id
                document.querySelector(".mainTitle").classList.add("updateElement");

                dataset.forEach(([k, v]) => {
                    if (k === "studygroups") v = v.split(',');
                    switch (k) {
                        case "groups":
                            if (v) {
                                const groupSelects = [...document.querySelectorAll("[name='groups[]']")];
                                const groups = v.split(',');
                                groupSelects.forEach((s) => s.selectedIndex = 0); // reset index
                                groups.forEach((g, i) => {
                                    const select = groupSelects[i];
                                    const options = [...select.options];
                                    select.selectedIndex = options.findIndex((o) =>
                                        o.innerText === g
                                    );
                                })
                            }
                            break;
                        default:
                            const input = document.querySelector(`[name=${k}],[name='${k}[]']`);
                            if (input.tagName.toLowerCase() !== "select") {
                                input.value = v;
                            } else {
                                v = Array.isArray(v) ? v : [v];
                                [...input.options].forEach((o) => o.selected = v.find(_v => _v === o.value || _v === o.innerText))
                            }
                            break;
                    }
                });


                //document.querySelector(".updateid").value = id


            }
        })
    </script>


</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'InStudy') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                        @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @endif

                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                        @endif
                        @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                                <a class="dropdown-item" target="_blank" href="https://lhcp1123.webapps.net:2083/cpsess9497228953/3rdparty/phpMyAdmin/db_structure.php?server=1&db=hj2jbnva_lilly.instudy">DB (PHPMyAdmin)</a>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- <main class="py-4">
            <div class="container">-->
        <div class="row justify-content-center" style="margin:0; padding-top:20px; flex-direction:column;align-items:center;justify-content:center;">
            @yield('beforecontent')
            @yield('content')
        </div>
        <!--</div>
        </main> -->
    </div>
</body>

</html>