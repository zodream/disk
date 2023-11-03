<?php
declare(strict_types=1);
namespace Zodream\Disk;

interface IStreamReader {

    public function read(int $length): string|false;
    public function readByte(): int;
    public function readChar(): string|false;
    public function readLine(int $length = 0): string|false;

    public function close(): void;
}