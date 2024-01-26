<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli;

use Kynx\Laminas\FormCli\ConfigProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

use function array_keys;
use function array_pop;
use function explode;
use function implode;
use function str_ends_with;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \Kynx\Laminas\FormCli\ConfigProvider
 * @psalm-import-type ConfigProviderArray from ConfigProvider
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
        $actual   = array_keys($this->getConfig());
        self::assertSame($expected, $actual);
    }

    #[DataProvider('filterParserProvider')]
    public function testInvokeReturnsAllFilterParsers(string $filterParser): void
    {
        $config = $this->getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-cli']['array-shape']['filter-parsers']);
        $filterProviders = $config['laminas-form-cli']['array-shape']['filter-parsers'];
        self::assertContains($filterParser, $filterProviders);
    }

    public static function filterParserProvider(): array
    {
        return self::getClasses('src/ArrayShape/Filter', 'Parser');
    }

    #[DataProvider('validatorParserProvider')]
    public function testInvokeReturnsAllValidatorParsers(string $filterParser): void
    {
        $config = $this->getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-cli']['array-shape']['validator-parsers']);
        $filterProviders = $config['laminas-form-cli']['array-shape']['validator-parsers'];
        self::assertContains($filterParser, $filterProviders);
    }

    public static function validatorParserProvider(): array
    {
        return self::getClasses('src/ArrayShape/Validator', 'Parser');
    }

    /**
     * @return ConfigProviderArray
     */
    private function getConfig(): array
    {
        return (new ConfigProvider())();
    }

    private static function getClasses(string $dir, string $suffix): array
    {
        $classes = [];

        $directory          = new RecursiveDirectoryIterator($dir);
        $iterator           = new RecursiveIteratorIterator($directory);
        $regex              = new RegexIterator($iterator, '#^src/(.+)\.php$#', RecursiveRegexIterator::GET_MATCH);
        $regex->replacement = '$1';
        /** @var list{string, string} $matches */
        foreach ($regex as $matches) {
            $file = array_pop($matches);
            if (! str_ends_with($file, $suffix)) {
                continue;
            }
            $parts           = explode(DIRECTORY_SEPARATOR, $file);
            $class           = 'Kynx\\Laminas\\FormCli\\' . implode('\\', $parts);
            $classes[$class] = [$class];
        }

        return $classes;
    }
}
