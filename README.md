# Simple files finder for PHP

## Install

```console
composer require antikirra/find
```

## Basic usage

```php
<?php

use Antikirra\Find\Find;

require __DIR__ . '/vendor/autoload.php';

$finder = Find::in('/path/to/dir');

$iterator = $finder->find(function (SplFileInfo $fileInfo) {
    return $fileInfo->getSize() === 0 ? $fileInfo : null;
});

foreach ($iterator as $realPath => $fileInfo) {
    // do stuff
}
```

## Demo

```php
<?php

use Antikirra\Find\Find;

require __DIR__ . '/vendor/autoload.php';

$finder = Find::in('/Users/antikirra/PhpstormProjects')
    ->filesOnly()
    //->withExtensions(['txt', 'php'])
    //->directoriesOnly()
    //->withSoftLimit(10)
    //->withHardLimit(10000)
;

$iterator = $finder->find(function (SplFileInfo $fileInfo) {
    // files modified within the last hour
    return time() - $fileInfo->getMTime() < 3600 ? $fileInfo : null;
});

foreach ($iterator as $realPath => $fileInfo) {
    echo $realPath . ' - ' . date('Y-m-d H:i:s', $fileInfo->getMTime()) . PHP_EOL;
}

// /Users/antikirra/PhpstormProjects/find/composer.lock - 2023-05-24 21:21:46
// /Users/antikirra/PhpstormProjects/find/README.md - 2023-05-24 21:31:57
// /Users/antikirra/PhpstormProjects/find/Find.php - 2023-05-24 21:21:14
// /Users/antikirra/PhpstormProjects/find/composer.json - 2023-05-24 21:21:14
// ...
```
