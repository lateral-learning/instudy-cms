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
        $data['studies'] = $this->DB->select(
            "SELECT instudy_studies.*,
            GROUP_CONCAT((SELECT groupName FROM instudy_groups WHERE groupId=`instudy_group-study`.groupRef)  SEPARATOR ',') AS gruppi
            FROM instudy_studies
            INNER JOIN `instudy_group-study`
            ON `instudy_group-study`.studyRef=instudy_studies.studyId
            GROUP BY instudy_studies.studyId, instudy_studies.name, instudy_studies.code, instudy_studies.productRef, instudy_studies.sectionRef, instudy_studies.categoryRef,
                     instudy_studies.studyOrder, instudy_studies.search, instudy_studies.type, instudy_studies.launcher, instudy_studies.date, instudy_studies.startDate, instudy_studies.endDate
        "
        );
        //$data['orders'] = $this->DB->select("SELECT studyOrder as i, name as item FROM instudy_studies");
        return view('cmsUploadStudio', $data);
    }
}
