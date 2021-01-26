@extends('layouts.action')

@section('content')

<div class="flex row w100">
    <div class="flex column w50 panelContainer" id="loadStudy">
        <h1>Carica uno studio</h1>
        <form action="./uploadStudy" method="POST" enctype="multipart/form-data" class="flex column">
            @csrf
            <label>
                File ZIP
                <input type="file" name="fileZIP" class="inputFile" required />
            </label>
            <label>
                Img PNG
                <input type="file" name="filePNG" class="inputFile" required />
            </label>
            <label>Nome studio:
                <input name="nameStudy" value="" />
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
                <select name="groups[]" multiple required>
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
                <select name="type">
                    <option value="story_v1" selected>story_v1</option>
                    <option value="story_v2">story_v2</option>
                </select>
            </label>
            <label>
                Ordine (numero)
                <!-- {{--
        <select name="order">
        @foreach ($orders as $order)
        <option value="{{ $order->i }}">{{ $order->i }} ({{ $order->item || 'Libero!' }})</option>
        @endforeach
        </select>
        --}} -->
                <input type="number" name="order" style="width:90px;" value="1" required />
                <p class="warner">Gli studi con lo stesso valore Ordine e tutti quelli successivi verranno "spinti" automaticamente di una posizione</p>
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
                    dom: 'Plfrtip'
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