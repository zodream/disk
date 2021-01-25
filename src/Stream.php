<?php
declare(strict_types=1);
namespace Zodream\Disk;
/**
 * 对流的处理，包含文件访问流等
 * @package Zodream\Disk
 * @author zodream
 * @version v1.0
 */
class Stream {
    /**
     * @var resource 流句柄
     */
    protected $stream = null;

    /**
     * @var bool 是否加锁
     */
    protected $useLocking = false;

    /**
     * @var File 文件路径
     */
    protected $file;

    public function __construct($file, $useLocking = false) {
        $this->useLocking = $useLocking;
        if (is_resource($file)) {
            $this->setStream($file);
            return;
        }
        if (!$file instanceof File) {
            $file = new File($file);
        }
        $this->file = $file;
    }

    /**
     * 设置指定的流
     * @param resource $stream
     * @return $this
     */
    public function setStream($stream) {
        $this->stream = $stream;
        return $this;
    }

    /**
     * 获取流
     * @return resource
     */
    public function getStream() {
        return $this->stream;
    }

    /**
     * 根据文件路径开始流的访问
     * @param string $mode  默认写入   r 为读取
     * @return $this
     */
    public function open($mode = 'a') {
        if (is_resource($this->stream)) {
            return $this;
        }
        $this->stream = fopen($this->file->getFullName(), $mode);
        return $this;
    }

    public function openRead() {
        return $this->open('r');
    }

    public function openWrite() {
        return $this->open('a');
    }

    /**
     * 是否是有效流
     * @return bool
     */
    public function isResource() {
        return $this->stream !== false && is_resource($this->stream);
    }

    /**
     * 判断是否已经结束
     * @return bool
     */
    public function isEnd() {
        return feof($this->stream);
    }

    /**
     * 读取一行
     * @param int $length 为空时表示获取一行
     * @return bool|string
     */
    public function readLine(int $length = 0) {
        $this->openRead();
        if (empty($length)) {
            return fgets($this->stream);
        }
        return fgets($this->stream, $length);
    }

    /**
     * 写入
     * @param mixed $content
     * @return $this
     */
    public function write($content) {
        $this->openWrite();
        if ($this->useLocking) {
            flock($this->stream, LOCK_EX);
        }
        fwrite($this->stream, (string)$content);
        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
        return $this;
    }

    /**
     * 写入一行
     * @param string $line
     * @return Stream
     */
    public function writeLine($line) {
        return $this->write($line.PHP_EOL);
    }

    /**
     * 写入多行
     * @param array $lines
     * @return Stream
     */
    public function writeLines(array $lines) {
        return $this->writeLine(implode(PHP_EOL, $lines));
    }

    /**
     * cli模式下输出当前内容
     * @return $this
     */
    public function flush() {
        fflush($this->stream);
        return $this;
    }

    /**
     * 读取指定长度
     * @param integer $length
     * @return bool|string
     */
    public function read(int $length) {
        $this->openRead();
        return fread($this->stream, $length);
    }

    /**
     * 移动操作位置
     * @param integer $offset
     * @param integer $whence
     * @return $this
     */
    public function move($offset, $whence = SEEK_SET) {
        fseek($this->stream, $offset, $whence);
        return $this;
    }

    /**
     * 关闭释放流
     * @return $this
     */
    public function close() {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
        return $this;
    }

}