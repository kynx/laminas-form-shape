<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Config as PsalmConfig;

#[CoversClass(ConfigLoader::class)]
final class ConfigLoaderTest extends TestCase
{
    public function testInitDecoratorConfigSetsMaxStringLength(): void
    {
        $expected = 1;
        ConfigLoader::load(maxStringLength: $expected);

        $actual = PsalmConfig::getInstance()->max_string_length;
        self::assertSame($expected, $actual);
    }
}
