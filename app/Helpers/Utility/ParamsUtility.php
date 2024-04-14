<?php

namespace App\Helpers\Utility;

class ParamsUtility
{
    public static function openFile($fileName){
        $path = storage_path() . "/framework/sessions/$fileName.txt";
        $fp = fopen($path, "w+");
        flock($fp, LOCK_EX);
        return $fp;
    }

    public static function closeFile($fileName, $fp, $delete = false){
        $path = storage_path() . "/framework/sessions/$fileName.txt";
        flock($fp, LOCK_UN);
        fclose($fp);
        if ($delete == true) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

}
