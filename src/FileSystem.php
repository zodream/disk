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
     * 合并路径
     * @param string|FileObject $base
     * @param string[] $items
     * @return string
     */
    public static function combine(string|FileObject $base, ...$items): string {
        $args = [];
        $baseFile = str_replace('\\', '/', (string)$base);
        foreach (explode('/', $baseFile) as $item) {
            if ($item === '.') {
                $item = '';
            } elseif ($item === '..') {
                if (count($args) === 0) {
                    $args[] = '';
                    continue;
                }
                if (count($args) === 1) {
                    continue;
                }
                array_pop($args);
                continue;
            }
            if ($item !== '' || count($args) === 0) {
                $args[] = $item;
            }
        }
        foreach ($items as $item) {
            $item = str_replace('\\', '/', (string)$item);
            foreach (explode('/', $item) as $it) {
                if ($it === '.') {
                    continue;
                }
                if ($item === '..') {
                    if (count($args) <= 1) {
                        continue;
                    }
                    array_pop($args);
                    continue;
                }
                if ($it !== '') {
                    $args[] = $it;
                }
            }
        }
        return implode('/', $args);
    }

	/**
	 * 遍历文件夹获取所有的文件
	 * @param string $directory
	 * @return array
	 */
	public static function files(string $directory): array {
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
	public static function getFile(string $file): ?string {
		if(is_file($file)) {
			return $file;
		}
		$vendor = dirname(__FILE__, 3);
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
	public static function getExtension(string $file, bool $point = false): string {
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
	public static function name(string $path): string {
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * 拓展名带 .
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function extension(string $path): string {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     * @return bool|string
     */
	public static function type(string $path): bool|string {
		return filetype($path);
	}

	/**
	 * Get the mime-type of a given file.
	 *
	 * @param  string  $path
	 * @return string|false
	 */
	public static function mimeType(string $path): bool|string {
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
	}

	/**
	 * 文件的尺寸
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function size(string $path): bool|int {
		return filesize($path);
	}

	/**
	 * 最后的更新时间
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function lastModified(string $path): bool|int {
		return filemtime($path);
	}

	/**
	 * 是文件夹
	 *
	 * @param  string  $directory
	 * @return bool
	 */
	public static function isDirectory(string $directory): bool {
		return is_dir($directory);
	}

	/**
	 * 是否能写
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public static function isWritable(string $path): bool {
		return is_writable($path);
	}

	/**
	 * 是文件
	 *
	 * @param  string  $file
	 * @return bool
	 */
	public static function isFile(string $file): bool {
		return is_file($file);
	}

	/**
	 * 正则匹配的文件
	 *
	 * @param  string  $pattern
	 * @param  int     $flags
	 * @return array
	 */
	public static function glob(string $pattern, int $flags = 0): array {
		return glob($pattern, $flags);
	}

	/**
	 * 获取文件内容
	 * @param string $file
	 * @return string
	 */
	public static function read(string $file): string {
		return file_get_contents(self::getFile($file));
	}

	/**
	 * 写入文件
	 * @param string $file
	 * @param string $data
	 * @param bool $lock
	 * @return bool|int
	 */
	public static function write(string $file, mixed $data, bool|int $lock = false): bool|int {
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	public static function exists(string $path): bool {
		return file_exists($path);
	}

	/**
	 * 在文件前面追加内容
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public static function prepend(string $path, string $data): bool|int {
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
	public static function append(string $path, mixed $data): bool|int {
		return file_put_contents($path, $data, FILE_APPEND);
	}

	/**
	 * 建立文件夹
	 *
	 * @param string $aimUrl
	 * @return bool
	 */
	public static function createDirectory(string $aimUrl): bool {
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
	public static function createFile(string $aimUrl, bool $overWrite = false): bool {
		if (is_file($aimUrl) && !$overWrite) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite) {
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
	public static function moveDirectory(string $oldDir, string $aimDir, bool $overWrite = false): bool {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = str_ends_with($aimDir, '/') ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = str_ends_with($oldDir, '/') ? $oldDir : $oldDir . '/';
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
	public static function moveFile(string $fileUrl, string $aimUrl, bool $overWrite = false): bool {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && !$overWrite) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite) {
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
	public static function deleteDirectory(string $aimDir): bool {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = str_ends_with($aimDir, '/') ? $aimDir : $aimDir . '/';
		if (!is_dir($aimDir)) {
			return false;
		}
		$dirHandle = opendir($aimDir);
		while (false !== ($file = readdir($dirHandle))) {
			if ($file === '.' || $file === '..') {
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
	public static function delete(string|array $paths): bool {
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
	public static function copyDirectory(string $oldDir, string $aimDir, bool $overWrite = false): bool {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = str_ends_with($aimDir, '/') ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = str_ends_with($oldDir, '/') ? $oldDir : $oldDir . '/';
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
        closedir($dirHandle);
		return true;
	}

	/**
	 * 复制文件
	 *
	 * @param string $fileUrl
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function copyFile(string $fileUrl, string $aimUrl, bool $overWrite = false): bool {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && !$overWrite) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite) {
			self::delete($aimUrl);
		}
		$aimDir = dirname($aimUrl);
        mkdir($aimDir);
		copy($fileUrl, $aimUrl);
		return true;
	}

	public static function isAbsolutePath(string $file): bool {
	    if (DIRECTORY_SEPARATOR == '/') {
	        return str_starts_with($file, '/');
        }
        return !!preg_match('#^[a-zA-Z]+:[\\\/]#', $file, $_);
    }

    public static function relativePath(string $base, string $path): string {
        if (!static::isAbsolutePath($path)) {
            return $path;
        }
        $base = str_replace('\\', '/', $base);
        $path = str_replace('\\', '/', $path);
        $base = rtrim($base, '/');
        if (str_starts_with($path, $base . '/')) {
            return substr($path, strlen($base) + 1);
        }
        $baseArr = explode('/', $base);
        $pathArr = explode('/', $path);
        $start = -1;
        $i = 0;
        $len = min(count($baseArr), count($pathArr));
        while ($i < $len) {
            if ($baseArr[$i] !== $pathArr[$i]) {
                break;
            }
            $start = $i;
            $i ++;
        }
        if ($start < 0) {
            return $path;
        }
        array_splice($pathArr, 0, $start + 1);
        return sprintf('%s%s', str_repeat('../', count($baseArr) - $start - 1),
            $path);
    }
}