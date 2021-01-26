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
        $data['products'] = $this->DB->select("SELECT productId as id, productName as name FROM instudy_products");
        $data['sections'] = $this->DB->select("SELECT sectionId as id, sectionName as name FROM instudy_sections");
        $data['categories'] = $this->DB->select("SELECT categoryId as id, name FROM instudy_categories");
        $data['groups'] = $this->DB->select("SELECT groupid as id, groupName as name FROM instudy_groups");
        $data['studies'] = $this->DB->select("SELECT * FROM instudy_studies");
        //$data['orders'] = $this->DB->select("SELECT studyOrder as i, name as item FROM instudy_studies");
        return view('cmsUploadStudio', $data);
    }
}
