<?php

namespace App\Http\Controllers;

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
        $updateStudyRef = !empty($request->input('updateid')) ? intval($request->input('updateid')) : 0;
        [$fileZIP, $filePNG, $fileZIPname] = $this->getFileData($request, $updateStudyRef);
        [$studyData, $groupData] = $this->getRequestData($request, $fileZIPname);
        $this->execQueries($studyData, $groupData, $updateStudyRef);
        $this->fileOperations($fileZIP, $filePNG, $fileZIPname, $studyData['launcher']);
        $this->DB->commit();
        return view('success');
    }

    protected function getFileData(Request $request, Int $updateStudyRef)
    {
        $fileZIP = $this->checkFile($request, "fileZIP", "zip", [$request->input('launcher'), "story_content/frame.xml"]);
        $filePNG = $this->checkFile($request, "filePNG", "png");
        if (empty($updateStudyRef)) {
            if (empty($fileZIP)) abort(422, "File ZIP assente o non valido");
            if (empty($filePNG)) abort(422, "File PNG assente o non valido");
        }
        $fileZIPname = $fileZIP
            ? pathinfo($fileZIP->getClientOriginalName())['filename']
            : $this->decodeSpaces($this->DB->select('SELECT code FROM instudy_studies WHERE studyId=?', [$updateStudyRef])[0]->code);
        return [$fileZIP, $filePNG, $fileZIPname];
    }

    protected function getRequestData(Request $request, $fileName)
    {
        $groupData = $request->input('studygroups'); // array
        $studyData = [
            "name" => is_string($request->input('namestudy')) ? $request->input('namestudy') : $fileName,
            "code" => $this->encodeSpaces($fileName),
            "product" => $request->input('product'),
            "section" => $request->input('section'),
            "category" => $request->input('category'),
            "order" => intval($request->input('order')),
            "search" => intval($request->input('search')),
            "type" => $request->input('type'),
            "launcher" => $request->input('launcher'),
            "startdate" => date("Y-m-d H:i:s", strtotime($request->input('startdate'))),
            "enddate" =>  date("Y-m-d H:i:s", strtotime($request->input('enddate')))
        ];
        return [$studyData, $groupData];
    }

    protected function fileOperations($fileZIP, $filePNG, String $fileZIPname, String $studyLauncher)
    {
        $ROOT = UploadFileController::ROOT;

        if (!empty($fileZIP)) {

            $folderPath = $this->extractZIP($fileZIP->path(), "$ROOT/projects/$fileZIPname/");

            // do modify only if it is a Storyline study
            if ($studyLauncher === "story.html" || $studyLauncher === "story_html5.html")
                $this->modifyFiles($folderPath, $studyLauncher);

            $this->createZIPFromFolder($folderPath, "$ROOT/projectsRepo/", "{$fileZIPname}.zip");
        }

        if (!empty($filePNG)) {
            $this->moveFile($filePNG, "{$fileZIPname}_IMG.png", "$ROOT/res/projectIcons/");
        }
    }

    protected function modifyFiles(String $folderPath, String $studyLauncher)
    {
        // $folderPath: la cartella dove sono i files in versione decompressa (es. projects/abc/)
        /*$ROOT = UploadFileController::ROOT;

        if ($studyLauncher === "story.html")
            $modContentFile = "$ROOT/utils/projData/modContent2.html";
        else if ($studyLauncher === "story_html5.html")
            $modContentFile = "$ROOT/utils/projData/modContent.html";

        $storyFile = $folderPath . $studyLauncher;
        $file_data = file_get_contents($modContentFile);
        $file_data .= file_get_contents($storyFile);
        file_put_contents($storyFile, $file_data);*/
    }

    protected function execQueries(array $studyData, array $groupData, Int $updateStudyRef)
    {
        $this->DB->beginTransaction();
        $updateOrder = !empty($updateStudyRef) ? " AND studyId!=$updateStudyRef" : '';
        $this->alterOrder->pushOrder($studyData['order'], "categoryRef={$studyData['category']} $updateOrder");
        if (empty($updateStudyRef)) {
            $studyRef = $this->insertStudy($studyData);
        } else {
            $this->updateStudy($studyData, $updateStudyRef);
            $this->deleteGroupStudyRelations($updateStudyRef);
        }
        $this->insertGroupStudyRelations($groupData, $studyRef ?? $updateStudyRef);
    }

    protected function insertStudy(array $studyData)
    {
        $this->DB->insert(
            "INSERT INTO instudy_studies (name,code,productRef,sectionRef,categoryRef,studyOrder,search,type,launcher,startDate,endDate)
             VALUES(?,?,?,?,?,?,?,?,?,?,?)",
            array_values($studyData) // strip off the keys
        );
        return $this->insertedID();
    }

    protected function updateStudy(array $studyData, Int $updateStudyRef)
    {
        $this->DB->update(
            "UPDATE instudy_studies SET name=?,code=?,productRef=?,sectionRef=?,categoryRef=?,studyOrder=?,search=?,type=?,launcher=?,startDate=?,endDate=?
             WHERE studyId=?",
            [...array_values($studyData), $updateStudyRef]
        );
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

    protected function deleteGroupStudyRelations(Int $updateStudyRef)
    {
        $this->DB->delete(
            "DELETE FROM `instudy_group-study` WHERE studyRef=?",
            [$updateStudyRef]
        );
    }
}
