@extends('layouts.action')

@section('content')

<div class="flex row w100">
    <div class="flex column w50 panelContainer" id="loadUser">
        <h1>Aggiungi un User</h1>
        <form action="./addUser" method="POST" class="flex column">
            @csrf
            <label>Nome user:
                <input name="nameUser" value="" style="width:220px;" required />
            </label>
            <label>
                Mail
                <input name="mail" value="" style="width:220px;" required />
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
                Divisione
                <input name="division" />
            </label>
            <label>
                Policy
                <select name="policy">
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </label>
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