<?php

namespace EmployeeBundle\Service;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EmployeeUploadService
{
    private $targetDir;

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
}