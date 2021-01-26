<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->DB = DB::connection("mysql2");
    }

    public function index(Request $request)
    {
        $passwordData = $this->getRequestData($request);
        $this->execQueries($passwordData);
        return view('success');
    }

    protected function getRequestData(Request $request)
    {
        $ref = intval($request->input('passwordRef'));
        if (!is_integer($ref)) {
            abort(422, "La chiave id della password non è un integer");
        }
        return [
            "passwordRef" => $ref,
        ];
    }

    protected function execQueries(array $passwordData)
    {
        //$this->DB->beginTransaction();
        $this->updatePassword($passwordData);
        //$this->DB->commit();
    }

    protected function updatePassword($passwordData)
    {
        $this->DB->insert("UPDATE instudy_passwords SET data=0 WHERE passwordId=" . $passwordData['passwordRef']);
    }
}
