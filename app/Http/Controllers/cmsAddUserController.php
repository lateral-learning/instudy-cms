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
        $data['users'] = $this->DB->select("SELECT instudy_users.* FROM instudy_users");
        return view('cmsAddUser', $data);
    }
}
