<?php

namespace App\Http\Controllers\Traits;

trait ConvertSpaces
{
    protected function encodeSpaces(String $str)
    {
        return str_replace(' ', '%23', $str);
    }
    protected function decodeSpaces(String $str)
    {
        return str_replace('%23', ' ', $str);
    }
}
