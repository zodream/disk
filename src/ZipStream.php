<?php
namespace Zodream\Disk;

use ZipArchive;

class ZipStream {

    /**
     * @var ZipArchive
     */
    protected $zip;

    public function __construct($file = null, $flags = null) {
        $this->zip = new ZipArchive();
        if (!empty($file)) {
            $this->open($file, $flags);
        }
    }

    /**
     * 打开文件流
     * @param $file
     * @param null $flags
     * @return $this
     */
    public function open($file, $flags = null) {
        $this->zip->open((string)$file, $flags);
        return $this;
    }

    /**
     * 创建文件流
     * @param $file
     * @return static
     */
    public static function create($file) {
        return new static($file, ZipArchive::CREATE);
    }

    /**
     * 添加文件
     * @param string|File $name 文件名
     * @param string|File $file 文件路径
     * @return $this
     */
    public function addFile($name, $file = null) {
        if ($name instanceof File) {
            list($name, $file) = [$name->getName(), $name];
        }
        $this->zip->addFile((string)$file, $name);
        return $this;
    }

    /**
     * 添加文件夹
     * @param $name
     * @param Directory $root
     * @return $this
     */
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

    /**
     * 直接写入文字
     * @param $name
     * @param $content
     * @return $this
     */
    public function addString($name, $content) {
        $this->zip->addFromString($name, $content);
        return $this;
    }

    /**
     * 解压到指定目录
     * @param $root
     * @return $this
     */
    public function extractTo($root) {
        $this->zip->extractTo((string)$root);
        return $this;
    }

    /**
     * 写入评论内容获取评论内容
     * @param string $content
     * @return $this|string
     */
    public function comment($content = null) {
        if (is_null($content)) {
            return $this->zip->getArchiveComment();
        }
        $this->zip->setArchiveComment($content);
        return $this;
    }

    /**
     * 关闭流
     */
    public function close() {
        if (!$this->zip) {
            return;
        }
        $this->zip->close();
    }

    public function __call($name, $arguments) {
        $this->zip->{$name}(...$arguments);
        return $this;
    }
}