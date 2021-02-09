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
        $deleteStudyRef = !empty($request->input('deleteid')) ? intval($request->input('deleteid')) : 0;
        if (empty($deleteStudyRef))
            [$fileZIP, $filePNG, $fileZIPname] = $this->getFileData($request, $updateStudyRef);
        [$studyData, $groupData, $newData] = $this->getRequestData($request, $fileZIPname ?? "");
        $studyRef = $this->execQueries($studyData, $groupData ?? [], $newData, $updateStudyRef, $deleteStudyRef);
        if (empty($deleteStudyRef))
            $this->fileOperations($fileZIP, $filePNG, $fileZIPname, $studyData['launcher'], $studyRef ?? $updateStudyRef);
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
        $newData = [
            "newproduct" => $request->input('newproduct'),
            "newsection" => $request->input('newsection'),
            "newcategory" => $request->input('newcategory'),
            "newproductcolor" => $request->input('newproductcolor'),
            "newcategorycolor" => $request->input('newcategorycolor')
        ];
        return [$studyData, $groupData, $newData];
    }

    protected function fileOperations($fileZIP, $filePNG, String $fileZIPname, String $studyLauncher, Int $studyRef)
    {
        $ROOT = UploadFileController::ROOT;

        // questa riga rende il nome uguale all'id, levarla se non serve
        $fileZIPname = $studyRef;

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

    protected function execQueries(array $studyData, array $groupData, array $newData, Int $updateStudyRef, Int $deleteStudyRef)
    {
        $this->DB->beginTransaction();
        if (empty($deleteStudyRef)) {
            $orderSkipUpdated = !empty($updateStudyRef) ? " AND studyId!=$updateStudyRef" : '';
            $this->alterOrder->pushOrder($studyData['order'], "categoryRef={$studyData['category']} $orderSkipUpdated");
            $this->assignSubItems($studyData, $newData);
            if (empty($updateStudyRef)) {
                $studyRef = $this->insertStudy($studyData);
            } else {
                $this->updateStudy($studyData, $updateStudyRef);
                $this->deleteGroupStudyRelations($updateStudyRef);
            }
            $this->insertGroupStudyRelations($groupData, $studyRef ?? $updateStudyRef);
        } else {
            $this->deleteGroupStudyRelations($deleteStudyRef);
            $this->deleteStudy($deleteStudyRef);
        }
        return $studyRef ?? null;
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

    protected function deleteStudy(Int $deleteStudyRef)
    {
        $this->DB->update(
            "DELETE FROM instudy_studies WHERE studyId=?",
            [$deleteStudyRef]
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

    protected function insertSubItem(String $tableType, array $data)
    {
        $keys = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->DB->insert(
            "INSERT INTO `instudy_$tableType` ($keys) VALUES ($placeholders)",
            array_values($data)
        );
        return $this->insertedID();
    }

    protected function addSubItem(String $tableType, String $type, String $key, array &$studyData, array $newData)
    {
        if (!empty($newData["new$type"])) {
            $data = [$key => $newData["new$type"]];
            if (!empty($newData["new{$type}color"])) $data['colour'] = $newData["new{$type}color"];
            $studyData[$type] =  $this->insertSubItem($tableType, $data);
        }
    }
    protected function assignSubItems(array &$studyData, array $newData)
    {
        $this->addSubItem("products", "product", "productName", $studyData, $newData);
        $this->addSubItem("sections", "section", "sectionName", $studyData, $newData);
        $this->addSubItem("categories", "category", "name", $studyData, $newData);
    }
}
