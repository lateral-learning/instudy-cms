<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class cmsUploadFileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];
        $data['products'] = DB::connection("mysql2")->select("SELECT productId as id, productName as name FROM instudy_products");
        $data['sections'] = DB::connection("mysql2")->select("SELECT sectionId as id, sectionName as name FROM instudy_sections");
        $data['categories'] = DB::connection("mysql2")->select("SELECT categoryId as id, name FROM instudy_categories");
        $data['groups'] = DB::connection("mysql2")->select("SELECT groupid as id, groupName as name FROM instudy_groups");
        return view('cmsUploadStudio', $data);
    }
}
