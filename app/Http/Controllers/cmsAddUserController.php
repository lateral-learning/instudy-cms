<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class cmsAddUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->DB = DB::connection("mysql2");
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];
        $data['groups'] = $this->DB->select("SELECT groupid as id, groupName as name FROM instudy_groups");
        $data['users'] = $this->DB->select(
            "SELECT instudy_users.*,
            GROUP_CONCAT((SELECT groupName FROM instudy_groups WHERE groupId=`instudy_user-group`.groupRef)  SEPARATOR ',') AS gruppi
            FROM instudy_users
            INNER JOIN `instudy_user-group`
            ON `instudy_user-group`.userRef=instudy_users.userId
            GROUP BY instudy_users.userId, instudy_users.email, instudy_users.name, instudy_users.firstLogin,
            instudy_users.lastLogin, instudy_users.totalAccesses, instudy_users.lastDevice, instudy_users.policy,
            instudy_users.division, instudy_users.passwordRef, instudy_users.fullView
        "
        );
        return view('cmsAddUser', $data);
    }
}
