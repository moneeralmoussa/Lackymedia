<?php

namespace AppBundle\Service;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    private $targetDir;

    private function detectFileEncoding($filePath){
        $str = file_get_contents($filePath);
        return mb_detect_encoding($str, "UTF-8, ASCII, Windows-1251, Windows-1252, Windows-1254, ISO-8859-1, ISO-8859-15");
    }

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->getTargetDir(), $fileName);
        return $this->getTargetDir().'/'.$fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }

    public function xlsLoader($fileName, $tryCsv = false){
        if (!$tryCsv) {
            $objPHPExcel = \PHPExcel_IOFactory::load($fileName);
        } else {
            $fileEncoding = $this->detectFileEncoding($fileName);
            $objReader = \PHPExcel_IOFactory::createReader('CSV');
            $objReader->setDelimiter(";");
            $objReader->setInputEncoding($fileEncoding);
            $objPHPExcel = $objReader->load($fileName);
        }

        $pColumn = 0;
        $pRow = 1;
        $objPHPExcel->setActiveSheetIndex(0);

        $lColumnNames = array();

        while (($cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($pColumn, $pRow)->getValue()) != "") {
            $lColumnNames[] = $cellValue;
            $pColumn++;
        }

        if(!$tryCsv && $pColumn <= 1) {
            return $this->xlsLoader($fileName, true);
        }

        return array($lColumnNames, $objPHPExcel);
    }

      public function uploadPhoto(UploadedFile $file)
      {
          $fileName = md5(uniqid()).'.'.$file->guessExtension();
          $file->move('uploads/', $fileName);
          return 'uploads/'.$fileName;
      }
}
