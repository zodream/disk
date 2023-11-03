<?php
declare(strict_types=1);
namespace Zodream\Disk;

interface IStreamWriter {

    public function write(mixed $content): static;
    public function writeByte(int $byte): static;
    public function writeLine(mixed $line): static;

    public function writeLines(array $lines): static;

    public function close(): void;
}