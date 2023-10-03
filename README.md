# disk
Disk

## 查找文件是否包含指定字符串

```php
$finder = new \Zodream\Disk\StreamFinder([
    ['<%', '%>'],
    '<?php',
    ['<?=', '?>']
]);
$finder->matchFile("6.gif"); // 返回是否包含
```