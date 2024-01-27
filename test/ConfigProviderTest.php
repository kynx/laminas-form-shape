<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli;

use Kynx\Laminas\FormCli\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
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
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 */
#[CoversClass(ConfigProvider::class)]
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

    #[DataProvider('filterVisitorsProvider')]
    public function testInvokeReturnsAllFilterVisitors(string $filterVisitor): void
    {
        $config = $this->getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-cli']['array-shape']['filter-visitors']);
        $filterVisitors = $config['laminas-form-cli']['array-shape']['filter-visitors'];
        self::assertContains($filterVisitor, $filterVisitors);
    }

    public static function filterVisitorsProvider(): array
    {
        return self::getClasses('src/ArrayShape/Filter', 'Visitor');
    }

    #[DataProvider('validatorVisitorsProvider')]
    public function testInvokeReturnsAllValidatorVisitors(string $validatorVisitor): void
    {
        $config = $this->getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-cli']['array-shape']['validator-visitors']);
        $validatorVisitors = $config['laminas-form-cli']['array-shape']['validator-visitors'];
        self::assertContains($validatorVisitor, $validatorVisitors);
    }

    public static function validatorVisitorsProvider(): array
    {
        return self::getClasses('src/ArrayShape/Validator', 'Visitor');
    }

    /**
     * @return FormCliConfigurationArray
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
