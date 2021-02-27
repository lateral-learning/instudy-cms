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
        [$userData, $groupData, $updateUserRef, $deleteUserRef] = $this->getRequestData($request);
        $this->execQueries($userData, $groupData, $updateUserRef, $deleteUserRef);
        return view('success');
    }

    protected function getRequestData(Request $request)
    {
        $groupData = array_values(array_unique(array_filter($request->input('groups'), function ($element) {
            return $element !== "" && $element !== NULL;
        })));
        $updateUserRef = !empty($request->input('updateid')) ? intval($request->input('updateid')) : 0;
        $deleteUserRef = !empty($request->input('deleteid')) ? intval($request->input('deleteid')) : 0;
        $userData = [
            "name" => $request->input('nameuser'),
            "email" => $request->input('mail'),
            "policy" => 0,
            "division" => !empty($request->input('division')) ? $request->input('division') : '',
        ];
        return [$userData, $groupData, $updateUserRef, $deleteUserRef];
    }

    protected function execQueries(array $userData, array $groupData, int $updateUserRef, Int $deleteUserRef)
    {
        $this->DB->beginTransaction();
        if (empty($deleteUserRef)) {
            if (empty($updateUserRef)) {
                $passwordRef = $this->insertPassword();
                $userRef = $this->insertUser($userData, $passwordRef);
            } else {
                $this->updateUser($userData, $updateUserRef);
                $this->deleteUserGroupRelations($updateUserRef);
            }
            $this->insertUserGroupRelations($userRef ?? $updateUserRef, $groupData);
        } else {
            $this->deleteUserGroupRelations($deleteUserRef);
            $this->deleteUser($deleteUserRef);
        }
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
            "INSERT INTO instudy_users (name,email,policy,division,passwordRef,lastDevice)
            VALUES(?,?,?,?,?,'')
        ",
            array_merge(array_values($userData), [$passwordRef]) // strip off the keys and add passwordRef
        );
        return $this->insertedID();
    }

    protected function updateUser(array $userData, int $updateUserRef)
    {
        $this->DB->update(
            "UPDATE instudy_users SET name=?, email=?, policy=?, division=? WHERE userId=?",
            array_merge(array_values($userData), [$updateUserRef])
        );
    }

    protected function deleteUser(int $deleteUserRef)
    {
        $this->DB->delete(
            "DELETE FROM instudy_users WHERE userId=?",
            [$deleteUserRef]
        );
    }

    protected function insertUserGroupRelations(Int $userRef, array $groupData)
    {
        $countGroups = count($groupData);
        if ($countGroups) {
            $listRelations = implode(",", array_map(function ($i) use ($userRef) {
                $order = $i + 1;
                return "($userRef,?,$order)";
            }, range(0, $countGroups - 1)));
            $this->DB->insert(
                "INSERT INTO `instudy_user-group` (userRef,groupRef,groupOrder) VALUES $listRelations",
                $groupData
            );
        }
    }

    protected function deleteUserGroupRelations(Int $updateUserRef)
    {
        $this->DB->delete(
            "DELETE FROM `instudy_user-group` WHERE userRef=?",
            [$updateUserRef]
        );
    }
}
