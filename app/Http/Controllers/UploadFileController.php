<?php

namespace App\Http\Controllers;

use Exception;
use Hamcrest\Type\IsInteger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\InsertedID;
use App\Http\Controllers\Injections\AlterOrder;
use App\Http\Controllers\Traits\ConvertSpaces;
use App\Http\Controllers\Traits\CheckFileUpload;

class UploadFileController extends Controller
{

    use InsertedID, ConvertSpaces, CheckFileUpload;

    const ROOT = __DIR__ . "/../../../../";

    public function __construct()
    {
        $this->middleware('auth');
        $this->DB = DB::connection("mysql2");
        $this->alterOrder = new AlterOrder($this->DB, "instudy_studies", "studyOrder");
    }

    public function index(Request $request)
    {
        [$fileZIP, $filePNG, $fileZIPname] = $this->getFileData($request);
        [$studyData, $groupData] = $this->getRequestData($request, $fileZIPname);
        $this->fileOperations($fileZIP, $filePNG, $fileZIPname);
        $this->execQueries($studyData, $groupData);
        return view('success');
    }

    protected function getFileData(Request $request)
    {
        $fileZIP = $this->checkFile($request, "fileZIP", "zip", ["story_html5.html", "story_content/frame.xml"]);
        $filePNG = $this->checkFile($request, "filePNG", "png");
        $fileZIPname = pathinfo($fileZIP->getClientOriginalName())['filename'];
        return [$fileZIP, $filePNG, $fileZIPname];
    }

    protected function getRequestData(Request $request, String $fileName)
    {
        $groupData = $request->input('groups'); // array
        $studyData = [
            "name" => is_string($request->input('nameStudy')) ? $request->input('nameStudy') : $fileName,
            "code" => $this->encodeSpaces($fileName),
            "product" => $request->input('product'),
            "section" => $request->input('section'),
            "category" => $request->input('category'),
            "order" => intval($request->input('order')),
            "search" => intval($request->input('search')),
            "type" => $request->input('type')
        ];
        return [$studyData, $groupData];
    }

    protected function fileOperations(\Illuminate\Http\UploadedFile $fileZIP, \Illuminate\Http\UploadedFile $filePNG, String $fileZIPname)
    {
        $ROOT = UploadFileController::ROOT;
        $folderPath = $this->extractZIP($fileZIP->path(), "$ROOT/projects/$fileZIPname/");
        $this->modifyFiles($folderPath);
        $this->createZIPFromFolder($folderPath, "$ROOT/projectsRepo/", "{$fileZIPname}.zip");
        $this->moveFile($filePNG, "{$fileZIPname}_IMG.png", "$ROOT/res/projectIcons/");
    }

    protected function modifyFiles(String $folderPath)
    {
        // $folderPath: la cartella dove sono i files in versione decompressa (es. projects/abc/)
    }

    protected function execQueries(array $studyData, array $groupData)
    {
        $this->DB->beginTransaction();
        $this->alterOrder->pushOrder($studyData['order'], "categoryRef={$studyData['category']}");
        $studyRef = $this->insertStudy($studyData);
        $this->insertGroupStudyRelations($groupData, $studyRef);
        $this->DB->commit();
    }

    protected function insertStudy(array $studyData)
    {
        $this->DB->insert(
            "
            INSERT INTO instudy_studies (name,code,productRef,sectionRef,categoryRef,studyOrder,search,type)
            VALUES(?,?,?,?,?,?,?,?)
        ",
            array_values($studyData) // strip off the keys
        );
        return $this->insertedID();
    }

    protected function insertGroupStudyRelations(array $groupData, Int $studyRef)
    {
        $countGroups = count($groupData);
        if ($countGroups) {
            $listRelations = implode(",", array_fill(0, $countGroups, "($studyRef,?)"));
            $this->DB->insert(
                "INSERT INTO `instudy_group-study` (studyRef,groupRef) VALUES $listRelations",
                $groupData
            );
        }
    }
}
