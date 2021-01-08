<?php
declare(strict_types=1);
namespace Zodream\Disk;
/**
 * 文件系统的访问基类
 * @package Zodream\Disk
 * @author zodream
 * @version v1.0
 */
abstract class FileObject {
    /**
     * @var string 文件名（含后缀）或文件夹名
     */
    protected string $name;

    /**
     * @var string 完整路径
     */
    protected string $fullName;

    /**
     * GET FILE/DIRECTORY NAME(not extension)
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * GET FILE/DIRECTORY FULL NAME
     * @return string
     */
    public function getFullName(): string {
        return $this->fullName;
    }

    /**
     * EXIST FILE/DIRECTORY
     * @return boolean
     */
    public function exist(): bool {
        return file_exists($this->fullName);
    }

    /**
     * IS FILE
     * @return bool
     */
    public function isFile(): bool {
        return is_file($this->fullName);
    }

    /**
     * IS DIRECTORY
     * @return bool
     */
    public function isDirectory(): bool {
        return is_dir($this->fullName);
    }

    /**
     * RENAME FILE
     * @param string $file
     * @param resource|null $context
     * @return bool
     */
    public function rename($file, $context = null) {
        if (is_resource($context)) {
            return rename($this->fullName, (string)$file, $context);
        }
        return rename($this->fullName, (string)$file);
    }

    /**
     * SET FILE MODE
     * @param int $mode
     * @return bool
     */
    public function chmod($mode) {
        return chmod($this->fullName, $mode);
    }
    
    abstract public function move($file);
    
    abstract public function copy($file);
    
    abstract public function delete();
    
    public function __toString() {
        return $this->getFullName();
    }

    /**
     * WINDOWS PATH TO LINUX PATH
     * @param string $file
     * @return string
     */
    public function getSafePath($file): string {
        return str_replace('\\', '/', $file);
    }

    /**
     * GET RELATIVE PATH, BUT PATH MUST BE ROOT'S CHILD
     * @param string $root
     * @return bool|string
     */
    public function getRelative($root) {
        if ($root instanceof FileObject) {
            $root = $root->getFullName();
        } else {
            $root = rtrim($this->getSafePath($root), '/');
        }
        if (strpos($this->fullName, $root) === 0) {
            return substr($this->fullName, strlen($root));
        }
        return false;
    }


    /**
     * 清除静态缓存
     * @return $this
     */
    public function clearStatCache() {
        clearstatcache();
        return $this;
    }
}