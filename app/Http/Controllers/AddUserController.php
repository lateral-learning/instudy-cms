<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\InsertedID;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddUserController extends Controller
{
    use InsertedID;
    public function __construct()
    {
        $this->middleware('auth');
        $this->DB = DB::connection("mysql2");
    }

    public function index(Request $request)
    {
        $userData = $this->getRequestData($request);
        $this->execQueries($userData);
        return view('success');
    }

    protected function getRequestData(Request $request)
    {
        return [
            "name" => $request->input('nameUser'),
            "email" => $request->input('mail'),
            "division" => intval($request->input('division')),
            "policy" => intval($request->input('policy')),
        ];
    }

    protected function execQueries(array $studyData)
    {
        $this->DB->beginTransaction();
        $passwordRef = $this->insertPassword();
        $this->insertUser($studyData, $passwordRef);
        $this->DB->commit();
    }

    protected function insertPassword()
    {
        $this->DB->insert("INSERT INTO instudy_passwords (data) VALUES (0)");
        return $this->insertedID();
    }

    protected function insertUser(array $studyData, $passwordRef)
    {
        $this->DB->insert(
            "
            INSERT INTO instudy_users (name,email,divisionRef,policy,passwordRef,lastDevice)
            VALUES(?,?,?,?,?,'')
        ",
            [...array_values($studyData), $passwordRef] // strip off the keys and add passwordRef
        );
        return $this->insertedID();
    }
}
