<?php

namespace App\Http\Controllers\Injections;

class AlterOrder
{
    public function __construct($DB, String $table, String $orderColumn)
    {
        $this->DB = $DB;
        $this->table = $table;
        $this->orderColumn = $orderColumn;
    }

    public function pushOrder(Int $orderValue, String $condition = "")
    {
        if (is_int($orderValue)) {
            $ANDcondition = $condition ? "AND $condition" : "";
            $this->DB->update(
                "UPDATE {$this->table} SET {$this->orderColumn}={$this->orderColumn}+1 WHERE {$this->orderColumn}>=$orderValue $ANDcondition"
            );
        } else {
            abort(422, "Il valore orderValue non è di tipo Integer");
        }
    }
}
