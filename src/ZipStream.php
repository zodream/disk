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

    public function open($file, $flags = null) {
        $this->zip->open((string)$file, $flags);
        return $this;
    }

    public function create($file) {
        $this->zip->open((string)$file, ZipArchive::CREATE);
        return $this;
    }

    public function addFile($name, $file) {
        $this->zip->addFile((string)$file, $name);
        return $this;
    }

    public function addDirectory($name, Directory $root) {
        $name = trim($name, '/');
        if (!empty($name)) {
            $name .= '/';
        }
        $root->map(function ($file) use ($name) {
           if ($file instanceof Directory) {
               $this->addDirectory($name.$file->getName(), $file);
               return;
           }
           $this->addFile($name.$file->getName(), $file);
        });
        return $this;
    }

    public function addString($name, $content) {
        $this->zip->addFromString($name, $content);
        return $this;
    }

    public function extractTo($root) {
        $this->zip->extractTo((string)$root);
        return $this;
    }

    public function comment($content = null) {
        if (is_null($content)) {
            return $this->zip->getArchiveComment();
        }
        $this->zip->setArchiveComment($content);
        return $this;
    }

    public function close() {
        $this->zip->close();
    }

    public function __call($name, $arguments) {
        $this->zip->{$name}(...$arguments);
        return $this;
    }
}