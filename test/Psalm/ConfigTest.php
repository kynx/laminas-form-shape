<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Config as PsalmConfig;

#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    public function testInitDecoratorConfigSetsMaxStringLength(): void
    {
        $expected = 1;
        Config::initDecoratorConfig(maxStringLength: $expected);

        $actual = PsalmConfig::getInstance()->max_string_length;
        self::assertSame($expected, $actual);
    }
}
