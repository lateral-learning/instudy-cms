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

        [name=delete][value=''] {
            display: none;
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
                document.querySelector("[name=delete]").value = "Cancella Elemento";
                document.querySelector("[name=invia]").value = "Update";

                dataset.forEach(([k, v]) => {
                    if (k === "studygroups") v = v.split(',');
                    switch (k) {
                        case "groups":
                            const groupSelects = [...document.querySelectorAll("[name='groups[]']")];
                            if (v) {
                                const groups = v.split(',');
                                changeNumberOfDraggables(() => groups.length);
                                groups.forEach((g, i) => {
                                    const select = groupSelects[i];
                                    const options = [...select.options];
                                    select.selectedIndex = options.findIndex((o) =>
                                        o.innerText === g
                                    );
                                })
                            } else {
                                // se non ci sono gruppi bisogna manualmente resettare il primo a 0
                                groupSelects[0].selectedIndex = 0;
                            }
                            break;
                        case "users":
                            if (v) {
                                const userInputs = [...document.querySelectorAll("[name='users[]']")];
                                const users = v.split(', ');
                                userInputs.forEach((inp) => {
                                    inp.checked = users.find((u) => u === inp.parentNode.innerText)
                                })
                            }
                            break;
                        case "studies_x_group":
                            if (v) {
                                function k(_) {
                                    console.log(_);
                                    return _;
                                }
                                const studies = v.split(', ');
                                const studiesInfo = STUDIES.filter(S => studies.find(s => s.toString() === S.id.toString()));
                                const products = [...new Set(studiesInfo.map(si => si.productRef))]
                                const sections = (studiesInfo.map(si => si.sectionRef))
                                const categories = (studiesInfo.map(si => si.categoryRef))
                                const addProducts = window.addDraggables(".product");
                                const addSections = window.addDraggables(".section");
                                const addCategories = window.addDraggables(".category");
                                const addStudies = window.addDraggables(".study");
                                addProducts(() => products.length);
                                [...document.querySelectorAll("[name='product[]']")].forEach((s, j) => s.selectedIndex = [...s.options].findIndex(o => o.value.toString() === products[j].toString()));
                                [...document.querySelectorAll(".product")].forEach((p, j) => {
                                    const innerSections = [...new Set(sections.filter(sec => studiesInfo.find((s) => s.sectionRef.toString() === sec.toString() && s.productRef.toString() === products[j].toString())))]
                                    addSections(() => innerSections.length, p);
                                    [...p.querySelectorAll("[name='section[]']")].forEach((s, j) => s.selectedIndex = [...s.options].findIndex(o => o.value.toString() === innerSections[j].toString()));
                                    [...p.querySelectorAll(".section")].forEach((sr, jj) => {
                                        const innerCategories = [...new Set(categories.filter(cat => studiesInfo.find((s) => s.categoryRef.toString() === cat.toString() && s.sectionRef.toString() === innerSections[jj].toString() && s.productRef.toString() === products[j].toString())))]
                                        addCategories(() => innerCategories.length, sr);
                                        [...sr.querySelectorAll("[name='category[]']")].forEach((s, j) => s.selectedIndex = [...s.options].findIndex(o => o.value.toString() === innerCategories[j].toString()));
                                        [...sr.querySelectorAll(".category")].forEach((cr, jjj) => {
                                            const innerStudies = studiesInfo
                                                .filter(s => s.categoryRef.toString() === innerCategories[jjj].toString() && s.sectionRef.toString() === innerSections[jj].toString() && s.productRef.toString() === products[j].toString())
                                                .sort((a, b) => parseInt(a.studyOrder) - parseInt(b.studyOrder))
                                                .map(s => s.studyId);
                                            addStudies(() => innerStudies.length, cr);
                                            [...cr.querySelectorAll("[name='study[]']")].forEach((s, j) => s.selectedIndex = [...s.options].findIndex(o => o.value.toString() === innerStudies[j].toString()));
                                        })
                                    });
                                });
                                // vari cicli innestati
                                // x ogni product drag -> fai addSections
                                // sezioni e categorie li prendi dopo con filter
                                // li prendi filtrando studiesInfo dove il prodotto è uguale a quello corrente e poi mappando a sectionRef
                                // x ogni section drag -> add categ
                                // filtro simile ma filtri sia su prodotto che sezione e torni categoria
                                // x ogni cat drag -> studies
                                // gli studi vengono infinite ordinati con sort via studyOrder
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

            }
        })

        function makeDraggables() {
            let dragindex = 0;
            let dropindex = 0;
            let clone = "";

            function drag(e) {
                e.dataTransfer.setData("text", e.target.id);
            }

            function drop(e) {
                e.preventDefault();
                clone = e.target.closest(".draggableGroup").cloneNode(true);
                clone.querySelector("select").selectedIndex = e.target.closest(".draggableGroup").querySelector("select").selectedIndex;
                let data = e.dataTransfer.getData("text");
                let nodelist = document.getElementById("parent").childNodes;
                for (let i = 0; i < nodelist.length; i++) {
                    if (nodelist[i].id == data) {
                        dragindex = i;
                    }

                }
                document.getElementById("parent").replaceChild(document.getElementById(data), e.target.closest(".draggableGroup"));
                document.getElementById("parent").insertBefore(clone, document.getElementById("parent").childNodes[dragindex]);
            }

            function allowDrop(e) {
                e.preventDefault();
            }

            let numberOfDraggables = 1;

            function changeNumberOfDraggables(cb) {
                numberOfDraggables = Math.min(100, Math.max(1, cb(numberOfDraggables)));
                document.getElementById('groupsStyle').innerHTML =
                    `.draggableGroup:nth-of-type(n+${numberOfDraggables+1}) { display: none; }`;
                document.querySelectorAll(`.draggableGroup:nth-of-type(n+${numberOfDraggables+1}) select`).forEach((s) => s.selectedIndex = 0);
                return false;
            }
            return [drag, drop, allowDrop, changeNumberOfDraggables];
        }
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