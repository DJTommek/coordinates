<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
	->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_84)
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::PHP_84,
    ])
    ->withTypeCoverageLevel(0);
