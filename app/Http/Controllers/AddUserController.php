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
        [$userData, $groupData] = $this->getRequestData($request);
        $this->execQueries($userData, $groupData);
        return view('success');
    }

    protected function getRequestData(Request $request)
    {
        $groupData = $request->input('groups');
        $userData = [
            "name" => $request->input('nameUser'),
            "email" => $request->input('mail'),
            "policy" => intval($request->input('policy')),
            "division" => !empty($request->input('division')) ? $request->input('division') : '',
        ];
        return [$userData, $groupData];
    }

    protected function execQueries(array $userData, array $groupData)
    {
        $this->DB->beginTransaction();
        $passwordRef = $this->insertPassword();
        $userRef = $this->insertUser($userData, $passwordRef);
        $this->insertUserGroupRelations($userRef, $groupData);
        $this->DB->commit();
    }

    protected function insertPassword()
    {
        $this->DB->insert("INSERT INTO instudy_passwords (data) VALUES (0)");
        return $this->insertedID();
    }

    protected function insertUser(array $userData, $passwordRef)
    {
        $this->DB->insert(
            "
            INSERT INTO instudy_users (name,email,policy,division,passwordRef,lastDevice)
            VALUES(?,?,?,?,?,'')
        ",
            [...array_values($userData), $passwordRef] // strip off the keys and add passwordRef
        );
        return $this->insertedID();
    }

    protected function insertUserGroupRelations(Int $userRef, array $groupData)
    {
        $countGroups = count($groupData);
        if ($countGroups) {
            $listRelations = implode(",", array_fill(0, $countGroups, "($userRef,?)"));
            $this->DB->insert(
                "INSERT INTO `instudy_user-group` (userRef,groupRef) VALUES $listRelations",
                $groupData
            );
        }
    }
}
