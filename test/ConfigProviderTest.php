<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape;

use Generator;
use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\Validator\RegexVisitor;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Throwable;

use function array_keys;
use function array_pop;
use function explode;
use function implode;
use function str_ends_with;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsSections(): void
    {
        $expected = [
            'laminas-cli',
            'laminas-form-shape',
            'dependencies',
        ];
        $actual   = array_keys(self::getConfig());
        self::assertSame($expected, $actual);
    }

    #[DataProvider('filterVisitorsProvider')]
    public function testInvokeReturnsAllFilterVisitors(string $filterVisitor): void
    {
        $config = self::getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-shape']['filter-visitors']);
        $filterVisitors = $config['laminas-form-shape']['filter-visitors'];
        self::assertContains($filterVisitor, $filterVisitors);
    }

    /**
     * @return array<string, non-empty-list<string>>
     */
    public static function filterVisitorsProvider(): array
    {
        return self::getClasses('src/Filter', 'Visitor');
    }

    #[DataProvider('validatorVisitorsProvider')]
    public function testInvokeReturnsAllValidatorVisitors(string $validatorVisitor): void
    {
        $config = self::getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-shape']['validator-visitors']);
        $validatorVisitors = $config['laminas-form-shape']['validator-visitors'];
        self::assertContains($validatorVisitor, $validatorVisitors);
    }

    /**
     * @return array<string, non-empty-list<string>>
     */
    public static function validatorVisitorsProvider(): array
    {
        return self::getClasses('src/Validator', 'Visitor');
    }

    #[DataProvider('aliasProvider')]
    public function testAllAliasesResolve(ContainerInterface $container, string $alias): void
    {
        self::assertContainerHasDependency($container, $alias);
    }

    /**
     * @return Generator<string, list{ContainerInterface, string}>
     */
    public static function aliasProvider(): Generator
    {
        $container = self::getContainer();
        $config    = self::getConfig();
        /** @var array<class-string, class-string> $aliases */
        $aliases = $config['dependencies']['aliases'] ?? [];
        foreach (array_keys($aliases) as $alias) {
            yield $alias => [$container, $alias];
        }
    }

    #[DataProvider('factoryProvider')]
    public function testAllFactoriesResolve(ContainerInterface $container, string $dependency): void
    {
        self::assertContainerHasDependency($container, $dependency);
    }

    /**
     * @return Generator<string, list{ContainerInterface, string}>
     */
    public static function factoryProvider(): Generator
    {
        $container = self::getContainer();
        $config    = self::getConfig();
        /** @var array<class-string, class-string> $factories */
        $factories = $config['dependencies']['factories'] ?? [];
        foreach (array_keys($factories) as $dependency) {
            yield $dependency => [$container, $dependency];
        }
    }

    /**
     * @param non-empty-string $pattern
     * @param list<class-string<TString>> $narrow
     */
    #[DataProvider('regexPatternProvider')]
    public function testRegexPatternsValidate(RegexVisitor $visitor, string $pattern, array $narrow): void
    {
        $actual = $visitor->visit(new Regex($pattern), new Union([new TString()]));
        $types  = $actual->getAtomicTypes();
        self::assertNotEmpty($types);

        foreach ($types as $type) {
            self::assertContains($type::class, $narrow);
        }
    }

    /**
     * @return array<string, list{RegexVisitor, non-empty-string, list<class-string<TString>>}>
     */
    public static function regexPatternProvider(): array
    {
        $container = self::getContainer();
        $visitor   = $container->get(RegexVisitor::class);
        $config    = self::getConfig();

        /** @var array<non-empty-string, list<class-string<TString>>> $patterns */
        $patterns = $config['laminas-form-shape']['validator']['regex']['patterns'] ?? [];

        $tests = [];
        foreach ($patterns as $pattern => $narrow) {
            $tests[$pattern] = [$visitor, $pattern, $narrow];
        }

        return $tests;
    }

    private static function assertContainerHasDependency(ContainerInterface $container, string $dependency): void
    {
        self::assertTrue($container->has($dependency));
        try {
            $container->get($dependency);
        } catch (Throwable $e) {
            self::fail($e->getMessage());
        }
    }

    /**
     * @return FormShapeConfigurationArray
     */
    private static function getConfig(): array
    {
        return (new ConfigProvider())();
    }

    private static function getContainer(): ContainerInterface
    {
        return include __DIR__ . '/container.php';
    }

    /**
     * @return array<string, non-empty-list<string>>
     */
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
            $class           = 'Kynx\\Laminas\\FormShape\\' . implode('\\', $parts);
            $classes[$class] = [$class];
        }

        self::assertNotEmpty($classes);
        return $classes;
    }
}
