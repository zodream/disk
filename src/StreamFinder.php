<?php
declare(strict_types=1);
namespace Zodream\Disk;


class StreamFinder {

    /**
     * 匹配规则
     * @var array
     */
    protected array $items = [];
    /**
     * 匹配过程
     * @var array
     */
    protected array $processItems = [];
    /**
     * 匹配结果
     * @var array
     */
    protected array $matchItems = [];
    protected int $index = -1;

    /**
     * 匹配
     * @param array $items 匹配规则，[[首,尾], 字符串]
     * @param bool $isMatchFirst 是否只匹配第一个
     */
    public function __construct(
        array $items,
        /**
         * 是否只匹配第一个
         * @var bool
         */
        protected bool $isMatchFirst = true
    ) {
        foreach ($items as $k => $item) {
            if (is_int($k) || is_array($item)) {
                $this->addRule($item);
                continue;
            }
            $this->addRule([$k, $item]);
        }
    }

    protected function addRule(array|string $rule): void {
        if (empty($rule)) {
            return;
        }
        $this->items[] = is_array($rule) ? array_filter($rule, function ($k) {
            return !empty($k);
        }) : [$rule];
    }

    /**
     * 是否匹配到
     * @return bool
     */
    public function isMatched(): bool {
        return count($this->matchItems) > 0;
    }

    /**
     * 获取匹配的规则
     * @return array [[[startStr: string, endStr: string], startPos: int, endPos: int]]
     */
    public function result(): array {
        $items = [];
        foreach ($this->matchItems as $i => $item) {
            $items[] = [$this->items[$i], ...$item];
        }
        return $items;
    }

    public function matchFile(mixed $file): bool {
        $this->reset();
        $isOpen = $file instanceof Stream;
        $fs = $isOpen ? $file : new Stream($file);
        $fs->open('rb');
        while (!$fs->isEnd()) {
            $this->matchChar($fs->readChar());
            if ($this->isMatchFirst && $this->isMatched()) {
                break;
            }
        }
        if (!$isOpen) {
            $fs->close();
        }
        return $this->isMatched();
    }

    public function matchChar(string|bool $code): bool {
        if (is_bool($code)) {
            return false;
        }
        $this->index ++;
        foreach ($this->items as $i => $rule) {
            $this->checkProcess($i, $rule, $code);
            $this->tryPushProcess($i, $rule, $code);
        }
        return $this->isMatched();
    }

    public function matchByte(int $code): bool {
        $this->index ++;
        foreach ($this->items as $i => $rule) {
            $this->checkProcess($i, $rule, $code);
            $this->tryPushProcess($i, $rule, $code);
        }
        return $this->isMatched();
    }

    public function reset(): void {
        $this->matchItems = $this->processItems = [];
        $this->index = -1;
    }

    protected function checkProcess(int $i, array $rule, string|int $code): void {
        if (empty($this->processItems[$i])) {
            return;
        }
        $items = $this->processItems[$i];
        $this->processItems[$i] = [];
        foreach ($items as $item) {
            [$position, $ruleIndex, $codeIndex] = $item;
            $next = $codeIndex + 1;
            if ($this->isMatchCode(substr($rule[$ruleIndex], $next, 1), $code)) {
                $this->pushProcess($i, $rule, $ruleIndex, $next, $position);
                continue;
            }
            if ($codeIndex < 0) {
                $this->pushProcess($i, $rule, $ruleIndex, $codeIndex, $position);
                continue;
            }
        }
    }

    protected function tryPushProcess(int $i, array $rule, string|int $code): void {
        if (!$this->isMatchCode(substr($rule[0], 0, 1), $code)) {
            return;
        }
        $this->pushProcess($i, $rule, 0, 0, $this->index);
    }

    protected function pushProcess(int $i, array $rule, int $ruleIndex, int $codeIndex, int $position): void {
        if ($codeIndex === strlen($rule[$ruleIndex]) - 1) {
            $ruleIndex ++;
            $codeIndex = -1;
        }
        if ($ruleIndex >= count($rule)) {
            $this->matchItems[$i][] = [$position, $this->index];
            return;
        }
        $this->processItems[$i][] = [$position, $ruleIndex, $codeIndex];
    }

    protected function isMatchCode(string $input, int|string $code): bool {
        if (is_int($code)) {
            return ord($input) === $code;
        }
        return $input === $code;
    }
}