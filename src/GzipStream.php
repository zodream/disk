<?php
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

    public function open($file, $mode = 'a') {
        $this->stream = gzopen((string)$file, $mode);
        return $this;
    }

    public function openRead($file) {
        return $this->open($file, 'r');
    }

    public function openWrite($file) {
        return $this->open($file, 'wb9');
    }


    /**
     * 写入
     * @param string $content
     * @return $this
     */
    public function write($content) {
        gzwrite($this->stream, $content);
        return $this;
    }

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