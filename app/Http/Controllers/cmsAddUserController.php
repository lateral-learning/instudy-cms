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
        $data['divisions'] = $this->DB->select("SELECT divisionId as id, divisionName as name FROM instudy_divisions");
        $data['users'] = $this->DB->select("SELECT * FROM instudy_users");
        return view('cmsAddUser', $data);
    }
}
