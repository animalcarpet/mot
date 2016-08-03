<?php

namespace Core\Action;

use Core\File\FileInterface;

class FileAction
{
    private $file;

    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }
}
