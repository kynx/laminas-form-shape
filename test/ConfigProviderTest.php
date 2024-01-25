<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli;

use Kynx\Laminas\FormCli\ConfigProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * @covers \Kynx\Laminas\FormCli\ConfigProvider
 */
final class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsSections(): void
    {
        $expected = [
            'laminas-cli',
            'laminas-form-cli',
            'dependencies',
        ];
        $actual   = array_keys((new ConfigProvider())());
        self::assertSame($expected, $actual);
    }
}
