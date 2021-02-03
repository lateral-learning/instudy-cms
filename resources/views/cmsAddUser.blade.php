@extends('layouts.action')

@section('content')

<div class="flex row w100">
    <div class="flex column w50 panelContainer" id="loadUser">
        <h1 class="mainTitle">Aggiungi un User</h1>
        <form action="./addUser" method="POST" class="flex column">
            @csrf
            <input type="hidden" name="updateid" value="" />
            <label>Nome user:
                <input name="nameuser" value="" style="width:220px;" required />
            </label>
            <label>
                Mail
                <input name="mail" value="" style="width:220px;" required />
            </label>
            <label>
                Divisione
                <input name="division" />
            </label>
            <div id="parent">
                <script>
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
                </script>
                Groups
                <p class="warner">Le select con valori nulli o duplicati vengono automaticamente ignorate</p>
                @foreach (range(0,15) as $rangeIndex)
                <div class="draggableGroup" style="width:280px;background:#aaa;" id="draggable{{$rangeIndex}}" draggable="true" ondragstart="drag(event)" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <select name="groups[]">
                        <option value="" selected />Nessuna selezione</option>
                        @foreach ($groups as $group)

                        <option value="{{ $group->id }}" />{{ $group->name }}</option>

                        @endforeach
                    </select>
                    drag 🕂
                </div>
                @endforeach
                <p class="warner">Il numero di select è fisso ma può essere aumentato nel codice</p>
            </div>

            <input type="submit" value="Invia" onclick="if(this.form.checkValidity()){this.disabled=true; this.value='Attendere...'; this.form.submit();}" style="width:150px;margin-top:18px;margin-right:auto;" />
        </form>
    </div>
    <div class="flex column w50 panelContainer">
        <h1>Vedi utenti</h1>
        <button onclick="document.getElementById('loadUser').style.display = document.getElementById('loadUser').style.display === 'none' ? 'block':'none';">Toggle tutto schermo</button>
        <table class="tableUsers">
            <thead>
                <tr>
                    @foreach ($users[0] as $userColumn=>$__value)
                    <th>
                        {{$userColumn}}
                    </th>
                    @endforeach
                    <th>Reset PW</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    @foreach ($user as $value)
                    <td>
                        {{$value}}
                    </td>
                    @endforeach
                    <td><button>Reset PW</button></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    @foreach ($users[0] as $userColumn=>$__value)
                    <th>
                        {{$userColumn}}
                    </th>
                    @endforeach
                    <th class="hideInside"></th>
                </tr>
            </tfoot>
        </table>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.js"></script>
        <script>
            $(document).ready(function() {
                $('.tableUsers tfoot th').each(function() {
                    var title = $(this).text();
                    $(this).html('<input class="tableSearch" type="text" placeholder="Search ' + title + '" />');
                });

                var table = $('.tableUsers').DataTable({
                    searchPanes: {
                        viewTotal: true
                    },
                    order: [
                        [0, 'desc']
                    ],
                    dom: 'Plfrtip',
                    "columnDefs": [{
                        "render": function(data, type, row) {
                            return `<a class='linkUpdate' href='javascript:void(0);' ${toDataAttributes({updateid:row[0], mail:row[1], nameuser:row[2], division:row[8], groups:row[10]})}>${data}</a>`;
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

                const columnPassword = 9;
                const columnMail = 1;

                $('.tableUsers tbody').on('click', 'button', function() {
                    const data = table.row($(this).parents('tr')).data();
                    if (confirm("Vuoi resettare la pw?")) window.location.href = `./resetPassword?passwordRef=${data[columnPassword]}&mail=${data[columnMail]}`
                });
            });
        </script>
        </link>
    </div>
</div>

@endsection