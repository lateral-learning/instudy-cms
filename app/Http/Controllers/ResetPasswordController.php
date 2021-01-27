<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        $this->sendResetMail($passwordData);
        $this->DB->commit();
        return view('success');
    }

    protected function getRequestData(Request $request)
    {
        $tmpRef = intval($request->input('passwordRef'));
        $mail = $request->input('mail');
        if (!is_integer($tmpRef)) {
            abort(422, "La chiave id della password non è un integer");
        }
        return [
            "passwordRef" => strval($tmpRef),
            "mail" => $mail
        ];
    }

    protected function execQueries(array $passwordData)
    {
        $this->DB->beginTransaction();
        $this->updatePassword($passwordData);
        // $this->DB->commit();
    }

    protected function sendResetMail(array $passwordData)
    {
        Mail::to($passwordData['mail'])->send(new ResetPassword($passwordData['mail'], $passwordData['passwordRef']));
    }

    protected function updatePassword(array $passwordData)
    {
        $this->DB->insert("UPDATE instudy_passwords SET data=0 WHERE passwordId=" . $passwordData['passwordRef']);
    }
}
