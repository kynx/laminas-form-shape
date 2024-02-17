<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Config;

#[CoversClass(ConfigLoader::class)]
final class ConfigLoaderTest extends TestCase
{
    use ConfigLoaderTrait;

    protected function setUp(): void
    {
        self::resetConfig();
    }

    protected function tearDown(): void
    {
        self::resetConfig();
    }

    public function testLoadDefaultsMaxStringLength(): void
    {
        $expected = ConfigLoader::DEFAULT_STRING_LENGTH;
        ConfigLoader::load();

        $actual = Config::getInstance()->max_string_length;
        self::assertSame($expected, $actual);
    }

    public function testLoadSetsMaxStringLength(): void
    {
        $expected = 1;
        ConfigLoader::load(maxStringLength: $expected);

        $actual = Config::getInstance()->max_string_length;
        self::assertSame($expected, $actual);
    }

    public function testLoadDoesNotLoadTwice(): void
    {
        ConfigLoader::load(maxStringLength: 500);
        $expected = Config::getInstance();
        ConfigLoader::load(maxStringLength: 1);
        $actual = Config::getInstance();

        self::assertSame($expected, $actual);
    }
}
