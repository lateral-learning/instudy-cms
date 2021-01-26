<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

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
        abort(406, "Assente il file .$extension di nome $fieldName");
        return null;
    }

    protected function moveFile(\Illuminate\Http\UploadedFile $file, String $fileName, String $path)
    {
        $file->move($path, $fileName);
        return $path . $fileName;
    }

    protected function getZIP(String $filePath)
    {
        $zip = new \ZipArchive();
        $zip->open($filePath);
        return $zip;
    }

    protected function getFilesInsideZIP(String $filePath)
    {
        $zip = $this->getZIP($filePath);
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
        abort(
            422,
            "Sono assenti uno o più file nello zip RICHIESTI: " .
                implode(', ', $toCheckFiles) .
                " PRESENTI: " . implode(', ', $filesInside)
        );
        return false;
    }

    protected function extractZIP(String $filePath, String $destination)
    {
        $zip = $this->getZIP($filePath);
        $zip->extractTo($destination);
        $zip->close();
        return $destination;
    }
}
