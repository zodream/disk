<?php
namespace Zodream\Disk;

use ZipArchive;

class ZipStream {

    /**
     * @var ZipArchive
     */
    protected $zip;

    public function __construct($file = null) {
        $this->zip = new ZipArchive();
        if (!empty($file)) {
            $this->open($file);
        }
    }

    public function open($file) {
        $this->zip->open((string)$file);
        return $this;
    }

    public function addFile($name, $file) {
        $this->zip->addFile((string)$file, $name);
        return $this;
    }


    public function comment($content = null) {
        if (is_null($content)) {
            return $this->zip->getArchiveComment();
        }
        $this->zip->setArchiveComment($content);
        return $this;
    }
}