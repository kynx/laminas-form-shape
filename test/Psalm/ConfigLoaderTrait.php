<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-require-extends TestCase
 */
trait ConfigLoaderTrait
{
    protected static function resetConfig(): void
    {
        ConfigLoader::load(ConfigLoader::DEFAULT_STRING_LENGTH);
    }
}
