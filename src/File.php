<?php
declare(strict_types=1);
namespace Zodream\Disk;

/**
 * THIS IS CLASS FILE
 *      MAKE YOU FEEL EASY.
 * author: zodream
 * version: v1.0
 */
class File extends FileObject {

    /**
     * @var string EXTENSION (NO POINT)
     */
    protected string $extension = '';

    /**
     * @var string PARENT DIRECTORY (FULL NAME)
     */
    protected string $directory = '';

    public function __construct($file) {
        if ($file instanceof File) {
            $this->fullName = $file->getFullName();
            $this->name = $file->getName();
            $this->extension = $file->getExtension();
            $this->directory = $file->getDirectoryName();
        } else {
            $this->fullName = $this->getSafePath($file);
            $args = pathinfo($this->fullName);
            $this->name = $args['basename'];
            $this->extension = $args['extension'] ?? '';
            $this->directory = $args['dirname'];
        }
    }

    /**
     * SET REAL NAME
     * @param string $name
     * @return $this
     */
    public function setName(string $name) {
        $this->name = $name;
        $arg = pathinfo($name, PATHINFO_EXTENSION);
        if (!empty($arg)) {
            $this->extension = $arg;
        }
        return $this;
    }

    /**
     * 获取不带后缀的文件名
     * @return string
     */
    public function getNameWithoutExtension(): string {
        if (empty($this->extension)) {
            return $this->name;
        }
        return basename($this->name, '.'.$this->extension);
    }

    /**
     * SET EXTENSION
     * @param string $arg
     * @return $this
     */
    public function setExtension(string $arg) {
        $this->extension = ltrim($arg, '.');
        return $this;
    }

    /**
     * GET EXTENSION
     * @return string
     */
    public function getExtension(): string {
        return $this->extension;
    }

    /**
     * GET FILE TYE
     * @return string|bool
     */
    public function type(): string|bool {
        return filetype($this->fullName);
    }

    /**
     * GET FILE MIME
     * @return string|bool
     */
    public function mimeType(): string|bool {
        if (!class_exists('finfo')) {
            return false;
        }
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->fullName);
    }

    /**
     * GET A INSTANCE OF PARENT DIRECTORY
     * @return Directory
     */
    public function getDirectory(): Directory {
        return new Directory($this->directory);
    }

    /**
     * GET A NAME OF PARENT DIRECTORY
     * @return string
     */
    public function getDirectoryName(): string {
        return $this->directory;
    }

    /**
     * @return int
     */
    public function size(): int {
        return filesize($this->fullName);
    }

    /**
     * LAST ACCESS TIME
     * @return int
     */
    public function accessTime(): int {
        return fileatime($this->fullName);
    }

    /**
     * CREATE FILE TIME
     * @return int
     */
    public function createTime(): int {
        return filectime($this->fullName);
    }

    /**
     * UPDATE FILE TIME
     * @return int
     */
    public function modifyTime(): int {
        return filemtime($this->fullName);
    }

    /**
     * FILE EXIST
     * @return bool
     */
    public function exist(): bool {
        return $this->isFile();
    }

    /**
     * IT'S EXECUTABLE
     * @return bool
     */
    public function canExecute(): bool {
        return is_executable($this->fullName);
    }

    /**
     * IT'S READABLE
     * @return bool
     */
    public function canRead(): bool {
        return is_readable($this->fullName);
    }

    /**
     * IT'S WRITABLE
     * @return bool
     */
    public function canWrite(): bool {
        return is_writable($this->fullName);
    }

    /**
     * UPDATE FILE MODIFY TIME AND ACCESS TIME
     *      IF NOT EXIST, WILL CREATE
     * @param int|null $modifyTime
     * @param int|null $accessTime
     * @return bool
     */
    public function touch(?int $modifyTime = null, ?int $accessTime = null): bool {
        return touch($this->fullName, $modifyTime, $accessTime);
    }

    /**
     * GET FILE CONTENT
     * @return string|bool
     */
    public function read(): string|bool {
        return file_get_contents($this->fullName);
    }

    /**
     * PUT FILE CONTENT
     * @param string $data
     * @param bool|integer $lock
     * @return bool|int
     */
    public function write(mixed $data, bool|int $lock = false): bool|int {
        if (!is_string($data) && !is_integer($data)) {
            $data = var_export($data, true);
        }
        if (is_bool($lock)) {
            $lock = $lock ? LOCK_EX : 0;
        }
        return file_put_contents($this->fullName, $data, $lock);
    }

    /**
     * APPEND FILE
     * @param string $data
     * @return bool|int
     */
    public function append(mixed $data): bool|int {
        return file_put_contents($this->fullName, $data, FILE_APPEND);
    }

    /**
     * PREPEND FILE
     * @param string $data
     * @return bool|int
     */
    public function prepend(string $data): bool|int {
        if ($this->exist()) {
            return $this->write($data.$this->read());
        }
        return $this->write($data);
    }

    /**
     * MOVE FIFE TO
     * @param string $file
     * @return bool
     */
    public function move(mixed $file): bool {
        return $this->rename($file);
    }

    /**
     * COPY FILE TO
     * @param string $file
     * @return bool
     */
    public function copy(mixed $file): bool {
        $folder = $file instanceof File ? $file->getDirectory() : new Directory(pathinfo((string)$file, PATHINFO_DIRNAME));
        $folder->create();
        return copy($this->fullName, (string)$file);
    }

    /**
     * DELETE FILE SELF
     * @return bool
     */
    public function delete(): bool {
        return unlink($this->fullName);
    }

    /**
     * GET FILE MD5
     * @return string
     */
    public function md5(): string {
        return md5_file($this->fullName);
    }

    /**
     * 转化为 stream 方式读写
     * @return Stream
     */
    public function asStream(): Stream {
        return new Stream($this);
    }
}