@extends('layouts.action')

@section('content')

<h1>Carica uno studio</h1>
<form action="./uploadStudy" method="POST" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;flex-direction:column;">
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
    <input type="submit" value="Invia" onclick="this.disabled=true; this.value='Attendere...'; this.form.submit();" style="width:150px;margin-top:18px;margin-right:auto;" />

</form>

@endsection