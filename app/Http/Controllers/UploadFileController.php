<?php

namespace App\Http\Controllers;

use Exception;
use Hamcrest\Type\IsInteger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Int_;

class UploadFileController extends Controller
{

    use InsertedID, ConvertSpaces, CheckFileUpload;

    public function __construct()
    {
        $this->middleware('auth');
        $this->DB = DB::connection("mysql2");
        $this->alterOrder = new AlterOrder($this->DB, "instudy_studies", "studyOrder");
    }

    public function index(Request $request)
    {
        $fileZIP = $this->checkFile($request, "fileZIP", "zip", ["story_html5.html", "frame.xml"]);
        $filePNG = $this->checkFile($request, "filePNG", "png");
        $fileZIPname = $fileZIP->getClientOriginalName();
        $filePNGname = $filePNG->getClientOriginalName();
        [$studyData, $groupData] = $this->getRequestdata($request, $fileZIPname);
        $this->execQueries($studyData, $groupData);
        // TODO
        // muovere i file nelle cartelle corrette, sia zip che folder
        // fare una funzione per sasha per fargli modificare i file
        $this->moveFile($fileZIP, $fileZIPname, "path");
        $this->moveFile($filePNG, $filePNGname, "./res/projectIcons/{$studyData['code']}_IMG.png");
        return view('success');
    }

    protected function getRequestdata(Request $request, String $fileName)
    {
        $groupData = $request->input('groups'); // array
        $studyData = [
            "name" => $request->input('name') || $fileName,
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

    protected function execQueries(array $studyData, array $groupData)
    {
        $this->DB->beginTransaction();
        $studyRef = $this->insertStudy($studyData);
        $this->insertGroupStudyRelations($groupData, $studyRef);
        $this->alterOrder->pushOrder($studyData['order'], "categoryRef={$studyData['category']}");
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
                "INSERT INTO instudy_group-study (studyRef,groupRef) VALUES $listRelations",
                $groupData
            );
        }
    }
}

// dependency injection per alterare l'ordine

class AlterOrder
{
    public function __construct($DB, String $table, String $orderColumn)
    {
        $this->DB = $DB;
        $this->table = $table;
        $this->orderColumn = $orderColumn;
    }

    public function pushOrder(Int $orderValue, String $condition = "")
    {
        if (is_int($orderValue)) {
            $condition = $condition ? "AND $condition" : "";
            $this->DB->update(
                "UPDATE {$this->table} SET {$this->orderColumn}={$this->orderColumn}+1 WHERE {$this->orderColumn}<=$orderValue $condition"
            );
        } else {
            throw new Exception("Il valore orderValue non è di tipo Integer");
        }
    }
}

// tratto per prendere l'ultimo id inserito

trait InsertedID
{
    protected function insertedID()
    {
        return intval($this->DB->getPdo()->lastInsertId());
    }
}

// tratto per convertire gli spazi

trait ConvertSpaces
{
    protected function encodeSpaces(String $str)
    {
        return str_replace(' ', '%23', $str);
    }
    protected function decodeSpaces(String $str)
    {
        return str_replace('%23', ' ', $str);
    }
}

// tratto per gestire l'upload dei file

trait CheckFileUpload
{
    protected function checkFile(Request $request, String $fieldName, String $extension, array $toCheckFiles = [])
    {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            if ($file->isValid() && $file->getClientOriginalExtension() === $extension) {
                if ($extension !== "zip" || !count($toCheckFiles) || $this->checkFilesInsideZIP($file->path(), $toCheckFiles))
                    return $file;
            }
        }
        throw new Exception("Assente il file .$extension di nome $fieldName");
        return null;
    }

    protected function getFilesInsideZIP(String $filePath)
    {
        $zip = new \ZipArchive();
        $zip->open($filePath);
        $filesInside = [];
        for ($i = 0; $i < $zip->count(); $i++) {
            array_push($filesInside, $zip->getNameIndex($i));
        }
        return $filesInside;
    }

    protected function checkFilesInsideZIP(String $filePath, array $toCheckFiles)
    {
        $filesInside = $this->getFilesInsideZIP($filePath);
        $intersection = array_intersect($toCheckFiles, $filesInside);
        if (count($intersection) === count($toCheckFiles)) {
            return true;
        }
        throw new Exception("Sono assenti uno o più file nello zip: " . implode(', ', $toCheckFiles));
        return false;
    }

    protected function moveFile(\Illuminate\Http\UploadedFile $file, String $fileName, String $path)
    {
        $file->move($path, $fileName);
    }
}
