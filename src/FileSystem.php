<?php
declare(strict_types=1);
namespace Zodream\Disk;
/**
 * 文件操作
 *
 * @author zodream
 * @time 2015-12-1
 */
class FileSystem {

	/**
	 * 遍历文件夹获取所有的文件
	 * @param string $directory
	 * @return array
	 */
	public static function files($directory) {
		$glob = glob($directory.'/*');

		if ($glob === false) {
			return [];
		}
		return array_filter($glob, function ($file) {
			return filetype($file) == 'file';
		});
	}

	/**
	 * 获取完整路径
	 * @param string $file
	 * @return null|string
	 */
	public static function getFile($file) {
		if(is_file($file)) {
			return $file;
		}
		$vendor = dirname(dirname(dirname(__FILE__)));
		$file   = '/'. ltrim($file, '/');
		if (is_file($vendor.$file)) {
			return $vendor.$file;
		}
		$app = dirname(APP_DIR);
		if (is_file($app.$file)) {
			return $app.$file;
		}
		return null;
	}

	/**
	 * 获取文件的拓展名
	 * @param string $file
	 * @param bool $point 是否带点
	 * @return string
	 */
	public static function getExtension($file, $point = false) {
		$arg = strtolower(substr(strrchr($file, '.'), 1));
		if (empty($arg) || !$point) {
			return $arg;
		}
		return '.'.$arg;
	}


	/**
	 * Extract the file name from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function name($path) {
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * 拓展名带 .
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function extension($path) {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function type($path) {
		return filetype($path);
	}

	/**
	 * Get the mime-type of a given file.
	 *
	 * @param  string  $path
	 * @return string|false
	 */
	public static function mimeType($path) {
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
	}

	/**
	 * 文件的尺寸
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function size($path) {
		return filesize($path);
	}

	/**
	 * 最后的更新时间
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function lastModified($path) {
		return filemtime($path);
	}

	/**
	 * 是文件夹
	 *
	 * @param  string  $directory
	 * @return bool
	 */
	public static function isDirectory($directory) {
		return is_dir($directory);
	}

	/**
	 * 是否能写
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public static function isWritable($path) {
		return is_writable($path);
	}

	/**
	 * 是文件
	 *
	 * @param  string  $file
	 * @return bool
	 */
	public static function isFile($file) {
		return is_file($file);
	}

	/**
	 * 正则匹配的文件
	 *
	 * @param  string  $pattern
	 * @param  int     $flags
	 * @return array
	 */
	public static function glob($pattern, $flags = 0) {
		return glob($pattern, $flags);
	}

	/**
	 * 获取文件内容
	 * @param string $file
	 * @return string
	 */
	public static function read($file) {
		return file_get_contents(self::getFile($file));
	}

	/**
	 * 写入文件
	 * @param string $file
	 * @param string $data
	 * @param bool $lock
	 * @return bool|int
	 */
	public static function write($file, $data, $lock = false) {
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	public static function exists($path) {
		return file_exists($path);
	}

	/**
	 * 在文件前面追加内容
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public static function prepend($path, $data) {
		if (self::exists($path)) {
			return self::write($path, $data.self::read($path));
		}
		return self::write($path, $data);
	}

	/**
	 * 追加内容
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public static function append($path, $data) {
		return file_put_contents($path, $data, FILE_APPEND);
	}

	/**
	 * 建立文件夹
	 *
	 * @param string $aimUrl
	 * @return bool
	 */
	public static function createDirectory($aimUrl) {
		$aimUrl = str_replace('', '/', $aimUrl);
		$aimDir = '';
		$arr = explode('/', $aimUrl);
		$result = true;
		foreach ($arr as $str) {
			$aimDir .= $str . '/';
			if (!is_dir($aimDir)) {
				$result = mkdir($aimDir);
			}
		}
		return $result;
	}

	/**
	 * 建立文件
	 *
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function createFile($aimUrl, $overWrite = false) {
		if (is_file($aimUrl) && $overWrite == false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite == true) {
			self::delete($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		mkdir($aimDir);
		touch($aimUrl);
		return true;
	}

	/**
	 * 移动文件夹
	 *
	 * @param string $oldDir
	 * @param string $aimDir
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function moveDirectory($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!is_dir($aimDir)) {
			mkdir($aimDir);
		}
		@ $dirHandle = opendir($oldDir);
		if (!$dirHandle) {
			return false;
		}
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir . $file)) {
				self::moveFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				self::moveDirectory($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		closedir($dirHandle);
		return rmdir($oldDir);
	}

	/**
	 * 移动文件
	 *
	 * @param string $fileUrl
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function moveFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && $overWrite = false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite = true) {
			self::delete($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		mkdir($aimDir);
		rename($fileUrl, $aimUrl);
		return true;
	}

	/**
	 * 删除文件夹
	 *
	 * @param string $aimDir
	 * @return boolean
	 */
	public static function deleteDirectory($aimDir) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		if (!is_dir($aimDir)) {
			return false;
		}
		$dirHandle = opendir($aimDir);
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($aimDir . $file)) {
				self::delete($aimDir . $file);
			} else {
				self::deleteDirectory($aimDir . $file);
			}
		}
		closedir($dirHandle);
		return rmdir($aimDir);
	}

	/**
	 * 删除文件
	 *
	 * @param string|array $paths
	 * @return boolean
	 */
	public static function delete($paths) {
		$paths = is_array($paths) ? $paths : func_get_args();
		$success = true;

		foreach ($paths as $path) {
			try {
				if (! @unlink($path)) {
					$success = false;
				}
			} catch (\Exception $e) {
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * 复制文件夹
	 *
	 * @param string $oldDir
	 * @param string $aimDir
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function copyDirectory($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!is_dir($aimDir)) {
			mkdir($aimDir);
		}
		$dirHandle = opendir($oldDir);
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir . $file)) {
				self::copyFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				self:: copyDirectory($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		return closedir($dirHandle);
	}

	/**
	 * 复制文件
	 *
	 * @param string $fileUrl
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function copyFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && $overWrite == false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite == true) {
			self::delete($aimUrl);
		}
		$aimDir = dirname($aimUrl);
        mkdir($aimDir);
		copy($fileUrl, $aimUrl);
		return true;
	}

	public static function isAbsolutePath($file) {
	    if (DIRECTORY_SEPARATOR == '/') {
	        return strpos($file, '/') === 0;
        }
        return preg_match('#^[a-zA-Z]+:[\\\/]#', $file, $match);
    }

    public static function relativePath($base, $path) {
        if (!static::isAbsolutePath((string)$path)) {
            return $path;
        }
        $base = str_replace('\\', '/', (string)$base);
        $path = str_replace('\\', '/', (string)$path);
        $base = rtrim($base, '/');
        if (strpos($path, $base.'/') === 0) {
            return substr($path, strlen($base) + 1);
        }
        $base = explode('/', $base);
        $path = explode('/', $path);
        $start = -1;
        $i = 0;
        $len = min(count($base), count($path));
        while ($i < $len) {
            if ($base[$i] !== $path[$i]) {
                break;
            }
            $start = $i;
            $i ++;
        }
        if ($start < 0) {
            return $path;
        }
        array_splice($path, 0, $start + 1);
        return sprintf('%s%s', str_repeat('../', count($base) - $start - 1),
            implode('/', $path));
    }
}