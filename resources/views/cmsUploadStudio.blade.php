@extends('layouts.action')

@section('content')

<div class="flex row w100">
    <div class="flex column w50 panelContainer" id="loadStudy">
        <h1 class="mainTitle">Carica uno studio</h1>
        <form action="./uploadStudy" method="POST" enctype="multipart/form-data" class="flex column">
            @csrf
            <input type="hidden" name="updateid" value="" />
            <p class="warner onupdate">Puoi lasciare i campi zip e immagine vuoti se non devi cambiarli</p>
            <p class="warner onupdate">Attenzione: se si cambia lo zip bisogna anche rimettere l'immagine, anche se è la stessa di prima</p>
            <label>
                File ZIP
                <input type="file" name="fileZIP" class="inputFile" />
            </label>
            <label>
                Img PNG
                <input type="file" name="filePNG" class="inputFile" />
            </label>
            <label>Nome studio:
                <input name="namestudy" value="" />
                <p class="warner">Opzionale, si può anche lasciare vuoto per avere come nome il nome del file</p>
            </label>
            <label>
                Prodotto
                <select name="product">
                    @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Sezione
                <select name="section">
                    @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Categoria
                <select name="category">
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Groups
                <select name="studygroups[]" multiple required>
                    @foreach ($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <p class="warner">Selezionarne almeno uno, selezionarne più di uno usando CTRL + click</p>
            </label>
            <label>
                Ricerca attivata
                <select name="search">
                    <option value="1" selected>Si</option>
                    <option value="0">No</option>
                </select>
            </label>
            <label>
                Tipo
                <select name="type" onchange="document.querySelector('[name=launcher]').value=this.options[this.selectedIndex].getAttribute('data-launcher')">
                    <option value="story_v1" data-launcher="story_html5.html" selected>story_v1</option>
                    <option value="story_v2" data-launcher="story.html">story_v2</option>
                    <option value="video" data-launcher="video.mp4">video</option>
                    <option value="story_v1" data-launcher="story_html5.html">altro</option>
                </select>
            </label>
            <label>
                Launcher
                <input name="launcher" value="story_html5.html" />
            </label>
            <label>
                Ordine (numero)

                <!--{{--<select name="order">
                    @foreach (range(0,10) as $rangeIndex)
                    <option value="{{ $rangeIndex }}">{{ $rangeIndex }} ({{ $orders[$rangeIndex]->item ?? '-- Libero --' }})</option>
                @endforeach
                </select> --}} -->

                <input type="number" name="order" style="width:90px;" value="1" required />
                <p class="warner">Gli studi con lo stesso valore Ordine e tutti quelli successivi verranno "spinti" automaticamente di una posizione</p>
            </label>
            <label>Data inizio
                <input name="startdate" type="datetime-local" />
            </label>
            <label>Data fine
                <input name="enddate" type="datetime-local" />
            </label>
            <input type="submit" value="Invia" onclick="if(this.form.checkValidity()){this.disabled=true; this.value='Attendere...'; this.form.submit();}" style="width:150px;margin-top:18px;margin-right:auto;" />

        </form>


    </div>
    <div class="flex column w50 panelContainer">
        <h1>Vedi studi</h1>
        <button onclick="document.getElementById('loadStudy').style.display = document.getElementById('loadStudy').style.display === 'none' ? 'block':'none';">Toggle tutto schermo</button>
        <table class="tableStudies">
            <thead>
                <tr>
                    @foreach ($studies[0] as $studyColumn=>$__value)
                    <th>
                        {{$studyColumn}}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($studies as $study)
                <tr>
                    @foreach ($study as $value)
                    <td>
                        {{$value}}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    @foreach ($studies[0] as $studyColumn=>$__value)
                    <th>
                        {{$studyColumn}}
                    </th>
                    @endforeach
                </tr>
            </tfoot>
        </table>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.js"></script>
        <script>
            $(document).ready(function() {
                $('.tableStudies tfoot th').each(function() {
                    var title = $(this).text();
                    $(this).html('<input type="text" placeholder="Search ' + title + '" />');
                });

                var table = $('.tableStudies').DataTable({
                    searchPanes: {
                        viewTotal: true
                    },
                    order: [
                        [0, 'desc']
                    ],
                    dom: 'Plfrtip',
                    "columnDefs": [{
                        "render": function(data, type, row) {
                            return `<a class='linkUpdate' href='javascript:void(0);' ${toDataAttributes({
                                updateid:row[0], namestudy:row[1], product:row[3], section:row[4], category:row[5],
                                order: row[6], search: row[7], type: row[8], launcher:row[9],
                                startdate: row[11].replace(" ", "T"), enddate:row[12].replace(" ", "T"), studygroups: row[13]
                            })}>${data}</a>`;
                        },
                        "targets": 0
                    }]
                });

                table.columns().every(function() {
                    var that = this;
                    $('input', this.footer()).on('keyup change', function() {
                        if (that.search() !== this.value) {
                            that
                                .search(this.value)
                                .draw();
                        }
                    });
                });
            });
        </script>
        </link>
    </div>
</div>

@endsection