<?php
declare(strict_types=1);
namespace Zodream\Disk;

/**
 * 这是一个文件夹的操作类，实现对一个文件夹对象的访问操作
 * author: zodream
 * version: v1.0
 */
class Directory extends FileObject {
    /**
     * 初始化 载入文件夹路径
     * @param FileObject|string $directory 文件夹路径
     */
    public function __construct($directory) {
        if ($directory instanceof FileObject) {
            $this->fullName = $directory->getFullName();
            $this->name = $this->getName();
        } else {
            $this->fullName = rtrim($this->getSafePath($directory), '/');
            $this->name = basename($this->fullName);
        }
    }

    /**
     * REGEX TO MATCH FILE 正则匹配路径
     * @param string $pattern 通配符
     * @param int $flag
     * @return FileObject[] 文件集合
     */
    public function glob(string $pattern = '*', int $flag = 0) {
        $files = [];
        foreach (glob($this->fullName.'/'.$pattern, $flag) as $item) {
            if (is_dir($item)) {
                $files[] = new static($item);
                continue;
            }
            $files[] = new File($item);
        }
        return $files;
    }

    /**
     * CREATE DIRECTORY
     * @return bool 是否创建成功
     */
    public function create(): bool {
        if ($this->exist()) {
            return true;
        }
        $args = explode('/', str_replace('\\', '/', $this->fullName));
        $directory = '';
        $result = true;
        foreach ($args as $item) {
            $directory .= $item . '/';
            if (!is_dir($directory) && !empty($directory)) {
                $result = mkdir($directory);
            }
        }
        return $result;
    }

    /**
     * GET PARENT DIRECTORY
     * @return static
     */
    public function parent(): Directory {
        return new static(dirname($this->fullName));
    }

    /**
     * GET ALL CHILDREN OF DIRECTORY
     * @return FileObject[]
     */
    public function children(): array {
        $files = [];
        $this->map(function ($file) use (&$files) {
            $files[] = $file;
        });
        return $files;
    }

    /**
     * 封装对子文件及子文件夹的访问
     * @param callable $callback
     */
    public function map(callable $callback) {
        $handle = opendir($this->fullName);
        if (is_bool($handle)) {
            return;
        }
        while (false !== ($name = readdir($handle))) {
            if ($name == '.' || $name == '..') {
                continue;
            }
            $file = $this->fullName.'/'.$name;
            $result = call_user_func($callback,
                is_dir($file) ? new static($file) : new File($file));
            if ($result === false) {
                break;
            }
        }
    }

    /**
     * 判断输入的路径是否是当前文件夹的父级
     * @param string $file
     * @return bool
     */
    public function isParent(mixed $file): bool {
        return !empty($file) && str_starts_with($this->fullName, (string)$file);
    }

    /**
     * GET FILE BY NAME IN THIS DIRECTORY
     * @param string $name 文件名
     * @return bool|FileObject
     */
    public function child(string $name): bool|FileObject {
        $file = $this->getChild($name);
        if (is_dir($file)) {
            return new static($file);
        }
        if (is_file($file)) {
            return new File($file);
        }
        return false;
    }

    /**
     * GET CHILD FILE, ONLY ALLOW CHILD NOT '../' '//'
     * @param string $name 文件名
     * @return string
     */
    protected function getChild(string $name): string {
        return preg_replace('#/+#', '/', preg_replace('#\.*[\\/]+#', '/', $this->fullName . '/'. $name));
    }

    /**
     * FILE IN CHILDREN
     * @param string $name 文件名
     * @return bool
     */
    public function hasFile(string $name): bool {
        return is_file($this->getChild($name));
    }

    /**
     * DIRECTORY IN CHILDREN
     * @param string $name 文件夹名
     * @return bool
     */
    public function hasDirectory(string $name): bool {
        return is_dir($this->getChild($name));
    }

    /**
     * GET FILE BY NAME
     * @param string $name 文件名
     * @return File
     */
    public function childFile(string $name): File {
        if (empty($name)) {
            throw new \Exception('filename error');
        }
        return new File($this->getChild($name));
    }

    /**
     *
     * @param $name
     * @return File 文件名或相对路径
     */
    public function file(string $name): File {
        return $this->childFile($name);
    }

    /**
     * GET FILE OR CHILD FILE
     * @param string $file
     * @return File
     */
    public function getFile(string $file): File {
        if (is_file($file)) {
            return new File(realpath($file));
        }
        return $this->childFile($file);
    }

    /**
     * 子目录
     * @param string $name
     * @return Directory
     */
    public function directory(string $name): Directory {
        if (is_dir($name)) {
            return new static(realpath($name));
        }
        return $this->childDirectory($name);
    }

    /**
     * GET DIRECTORY BY NAME
     * @param string $name
     * @return static
     */
    public function childDirectory(string $name): Directory {
        return new static($this->getChild($name));
    }

    /**
     * EXIST DIRECTORY
     * @return bool
     */
    public function exist(): bool {
        return $this->isDirectory();
    }

    /**
     * GET FREE SPACE
     * @return bool|float
     */
    public function freeSpace(): bool|float {
        return disk_free_space($this->fullName);
    }

    /**
     * GET TOTAL SPACE
     * @return bool|float
     */
    public function totalSpace(): bool|float {
        return disk_total_space($this->fullName);
    }

    /**
     * ADD FILE IN DIRECTORY
     * @param string $name
     * @param string $data
     * @return File
     */
    public function addFile(string $name, mixed $data): File {
        $file = new File($this->getChild($name));
        $file->write($data);
        return $file;
    }

    /**
     * DELETE FILES IN CHILDREN
     * @param string $arg
     * @return bool
     */
    public function deleteFile(string $arg): bool {
        foreach (func_get_args() as $name) {
            (new File($this->getChild($name)))->delete();
        }
        return true;
    }

    /**
     * DELETE DIRECTORIES IN CHILDREN
     * @param string $arg
     * @return bool
     */
    public function deleteDirectory(string $arg): bool {
        foreach (func_get_args() as $name) {
            (new static($this->fullName.'/'.$name))->delete();
        }
        return true;
    }

    /**
     * ADD DIRECTORY IN DIRECTORY
     * @param string|null $name
     * @param int $mode
     * @return Directory
     */
    public function addDirectory(?string $name, int $mode = 0777): Directory {
        if (is_null($name)) {
            return $this;
        }
        $dir = $this->getChild($name);
        if (!is_dir($dir)) {
            mkdir($dir, $mode);
        }
        return new Directory($dir);
    }


    /**
     * 获取绝对路径
     * @param $file
     * @return string
     */
    public function getAbsolute(string $file): string {
        if (is_file($file)) {
            return $file;
        }
        $path = $this->fullName.'/'.$file;
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * MOVE TO DIRECTORY
     * @param string $file
     * @return bool
     */
    public function move(mixed $file): bool {
        $result = true;
        $this->map(function (FileObject $item) use (&$result, $file) {
            $result = $result && $item->move($file. '/'. $item->getName());
        });
        return $result;
    }

    /**
     * COPY TO DIRECTORY
     * @param string $file
     * @return bool
     */
    public function copy(mixed $file): bool {
        $result = true;
        $this->map(function (FileObject $item) use (&$result, $file) {
            $result = $result && $item->copy($file. '/'. $item->getName());
        });
        return $result;
    }

    /**
     * DELETE SELF
     * @return bool
     */
    public function delete(): bool {
        $this->clear();
        @rmdir($this->fullName);
        return true;
    }

    /**
     * 清空文件夹
     * @return $this
     */
    public function clear() {
        $children = $this->children();
        foreach ($children as $file) {
            $file->delete();
        }
        return $this;
    }
}