<?php
namespace Zodream\Disk;

use Exception;

/**
 * 文件系统的错误处理类
 * @package Zodream\Disk
 * @author zodream
 * @version v1.0
 */
class FileException extends Exception {

    public function __construct($message, $code = 99, Exception $previous = null) {
        if ($message instanceof FileObject) {
            $message .= ' HAS ERROR!';
        }
        parent::__construct($message, $code, $previous);
    }

    public function getName() {
        return 'FileException';
    }
}