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
    protected static function tearDownConfig(): void
    {
        ConfigLoader::load(ConfigLoader::DEFAULT_STRING_LENGTH);
        $loaderReflection = new ReflectionClass(ConfigLoader::class);
        $loaderReflection->setStaticPropertyValue('loaded', false);
    }
}
