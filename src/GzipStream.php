<?php
declare(strict_types=1);
namespace Zodream\Disk;

class GzipStream {

    /**
     * @var resource
     */
    protected $stream;

    public function __construct($file = null) {
        if (!empty($file)) {
            $this->open($file);
        }
    }

    /**
     * 以gzip 流的形式打开文件
     * @param string $file
     * @param string $mode
     * @return $this
     */
    public function open(mixed $file, string $mode = 'a') {
        $this->stream = gzopen((string)$file, $mode);
        return $this;
    }

    /**
     * 以只读流的形式打开文件
     * @param $file
     * @return $this
     */
    public function openRead(mixed $file) {
        return $this->open($file, 'r');
    }

    /**
     * 以只写流的形式打开文件
     * @param $file
     * @return $this
     */
    public function openWrite(mixed $file) {
        return $this->open($file, 'wb9');
    }


    /**
     * 写入
     * @param string $content
     * @return $this
     */
    public function write(string $content) {
        gzwrite($this->stream, $content);
        return $this;
    }

    /**
     * 关闭流
     * @return $this
     */
    public function close() {
        if (is_resource($this->stream)) {
            gzclose($this->stream);
        }
        $this->stream = null;
        return $this;
    }

    /**
     * 压缩文件
     * @param File $distFile
     * @param File $sourceFile
     */
    public static function compress(File $distFile, File $sourceFile) {
        $distStream = new static();
        $distStream->openWrite($distFile);
        $srcStream = new Stream($sourceFile);
        $srcStream->openRead();
        while (!$srcStream->isEnd()) {
            $distStream->write($srcStream->read(1024 * 256));
        }
        $srcStream->close();
        $distStream->close();
    }
}