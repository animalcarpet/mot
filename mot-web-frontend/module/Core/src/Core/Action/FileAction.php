<?php

namespace Core\Action;

use Core\File\FileInterface;

class FileAction implements ActionResultInterface
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
