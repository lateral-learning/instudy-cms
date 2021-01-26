<?php

namespace App\Http\Controllers\Traits;

trait InsertedID
{
    protected function insertedID()
    {
        return intval($this->DB->getPdo()->lastInsertId());
    }
}
