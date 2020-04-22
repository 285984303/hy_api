<?php namespace App\Models;
/**
 * pdf png互转类
 * Created by PhpStorm.
 * User: gaowen
 * Date: 5/31/16
 * Time: 12:01 PM
 */

class PdfPngChange {
    //资源文件地址
    public $filePath;
    //保存地址
    public $sourcePath;

public function __construct()
{
    $this->filePath= public_path('upload').'/a.pdf';
    $this->sourcePath==public_path('upload');
}

    function pdf2png($PDF,$Path){
        $PDF = $this->filePath;
        $Path = $this->sourcePath;
        if(!extension_loaded('imagick')){
            return false;
        }
        if(!file_exists($PDF)){
            return false;
        }
        $IM =new imagick();
        $IM->setResolution(120,120);
        $IM->setCompressionQuality(100);
        $IM->readImage($PDF);
        foreach($IM as $Key => $Var){
            $Var->setImageFormat('png');
            $Filename = $Path.'/'.md5($Key.time()).'.png';
            if($Var->writeImage($Filename)==true){
                $Return[]= $Filename;
            }
        }
        return $Return;
    }

}