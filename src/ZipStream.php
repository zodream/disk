<?php
declare(strict_types=1);
namespace Zodream\Disk;

use ZipArchive;

class ZipStream {

    /**
     * @var ZipArchive
     */
    protected $zip;
    protected $file;

    public function __construct($file = null, int $flags = \ZipArchive::RDONLY) {
        $this->zip = new ZipArchive();
        if (!empty($file)) {
            $this->open($file, $flags);
        }
    }

    /**
     * 打开文件流
     * @param $file
     * @param int $flags
     * @return $this
     */
    public function open(mixed $file, int $flags = \ZipArchive::RDONLY) {
        $this->zip->open((string)$file, $flags);
        $this->file = $file;
        return $this;
    }

    /**
     * 创建文件流
     * @param $file
     * @return static
     */
    public static function create(mixed $file) {
        return new static($file, ZipArchive::CREATE);
    }

    /**
     * 添加文件
     * @param string|File $name 文件名
     * @param string|File $file 文件路径
     * @return $this
     */
    public function addFile(mixed $name, mixed $file = null) {
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
    public function addDirectory(string $name, Directory $root) {
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
    public function addString(string $name, string $content) {
        $this->zip->addFromString($name, $content);
        return $this;
    }

    /**
     * 解压到指定目录
     * @param $root
     * @return $this
     */
    public function extractTo(mixed $root) {
        // $this->zip->extractTo((string)$root);
        if (!$root instanceof Directory) {
            $root = new Directory($root);
        }
        $root->create();
        $length = $this->zip->numFiles;
        for($i = 0; $i < $length; $i++) {
            $statInfo = $this->zip->statIndex($i);
            if ($statInfo['crc'] === 0) {
                //新建目录
                $root->directory(substr($statInfo['name'], 0,-1))->create();
                continue;
            }
            copy('zip://'.$this->file.'#'.$statInfo['name'],
                (string)$root->file($statInfo['name']));
        }
        return $this;
    }

    /**
     * 遍历文件
     * @param callable $func (string $name, bool $isFolder)
     * @return $this
     */
    public function each(callable $func) {
        $length = $this->zip->numFiles;
        for($i = 0; $i < $length; $i++) {
            $statInfo = $this->zip->statIndex($i);
            if ($statInfo['crc'] === 0) {
                //新建目录
                $func(substr($statInfo['name'], 0,-1), true);
                continue;
            }
            $func($statInfo['name'], false);
        }
        return $this;
    }

    /**
     * 写入评论内容获取评论内容
     * @param string $content
     * @return $this|string
     */
    public function comment(?string $content = null) {
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