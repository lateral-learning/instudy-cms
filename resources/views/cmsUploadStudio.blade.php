@extends('layouts.action')

@section('content')

<h1>Carica uno studio</h1>
<form action="./uploadStudy" method="POST" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;flex-direction:column;">
    @csrf
    <label>
        File ZIP
        <input type="file" name="fileZIP" style="min-width:600px;height:90px;border:1px solid black;background:rgb(245,245,245);" />
    </label>
    <label>
        Img PNG
        <input type="file" name="filePNG" style="min-width:600px;height:90px;border:1px solid black;background:rgb(245,245,245);" />
    </label>
    <label>Nome studio:
        <input name="name" />
        <p style="font-size:0.8rem;color:orangered;margin-bottom:0;">Opzionale, si può anche lasciare vuoto per avere come nome il nome del file</p>
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
        <select name="groups[]" multiple>
            @foreach ($groups as $group)
            <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </select>
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
        <input type="number" name="order" style="width:90px;" value="1" />
        <!-- <p style="font-size:0.8rem;color:orangered;margin-bottom:0;">Lasciare vuoto per metterlo alla fine</p> -->
    </label>
    <input type="submit" value="Invia" style="width:150px;margin-top:18px;margin-right:auto;" />

</form>

@endsection