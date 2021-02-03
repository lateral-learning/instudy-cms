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
        //abort(406, "Assente il file .$extension di nome $fieldName");
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
        return null;
    }

    protected function extractZIP(String $filePath, String $destination)
    {
        $zip = $this->getZIP($filePath);
        $zip->extractTo($destination);
        $zip->close();
        return $destination;
    }

    protected function createZIPFromFolder(String $folderPath, String $zipPath, String $zipFileName)
    {
        $rootPath = realpath($folderPath);
        $zip = new \ZipArchive();
        $zip->open($zipPath . $zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            } else {
                $end2 = substr($file, -2);
                if ($end2 == "/.") {
                    $folder = substr($file, 0, -2);
                    $zip->addEmptyDir($folder);
                }
            }
        }
        $zip->close();
    }
}
