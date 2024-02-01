<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Config as PsalmConfig;

final class Config extends PsalmConfig
{
    public static function initDecoratorConfig(int $maxStringLength): void
    {
        new self();
        $config                    = self::getInstance();
        $config->max_string_length = $maxStringLength;
    }
}
