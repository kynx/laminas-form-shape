<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @psalm-require-extends TestCase
 */
trait ConfigLoaderTrait
{
    protected static function reloadConfig(int $maxStringLength = 500): void
    {
        self::tearDownConfig();
        ConfigLoader::load($maxStringLength);
    }

    protected static function tearDownConfig(): void
    {
        $loaderReflection = new ReflectionClass(ConfigLoader::class);
        $loaderReflection->setStaticPropertyValue('loaded', false);
    }
}
