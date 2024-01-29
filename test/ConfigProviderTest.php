<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli;

use Generator;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexVisitor;
use Kynx\Laminas\FormCli\ConfigProvider;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 * @psalm-import-type VisitedArray from AbstractVisitedType
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
        $actual   = array_keys(self::getConfig());
        self::assertSame($expected, $actual);
    }

    #[DataProvider('filterVisitorsProvider')]
    public function testInvokeReturnsAllFilterVisitors(string $filterVisitor): void
    {
        $config = self::getConfig();
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
        $config = self::getConfig();
        /** @psalm-suppress RedundantConditionGivenDocblockType We're testing that docblock is correct */
        self::assertIsArray($config['laminas-form-cli']['array-shape']['validator-visitors']);
        $validatorVisitors = $config['laminas-form-cli']['array-shape']['validator-visitors'];
        self::assertContains($validatorVisitor, $validatorVisitors);
    }

    public static function validatorVisitorsProvider(): array
    {
        return self::getClasses('src/ArrayShape/Validator', 'Visitor');
    }

    #[CoversNothing]
    #[DataProvider('aliasProvider')]
    public function testAllAliasesResolve(ContainerInterface $container, string $alias): void
    {
        self::assertContainerHasDependency($container, $alias);
    }

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

    #[CoversNothing]
    #[DataProvider('factoryProvider')]
    public function testAllFactoriesResolve(ContainerInterface $container, string $dependency): void
    {
        self::assertContainerHasDependency($container, $dependency);
    }

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
     * @param VisitedArray $existing
     */
    #[CoversNothing]
    #[DataProvider('regexPatternProvider')]
    public function testRegexPatternsValidate(
        RegexVisitor $visitor,
        string $pattern,
        array $existing,
        array $expected
    ): void {
        $actual = $visitor->visit(new Regex($pattern), $existing);
        self::assertEquals($expected, $actual);
    }

    public static function regexPatternProvider(): array
    {
        $container = self::getContainer();
        $visitor   = $container->get(RegexVisitor::class);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'color'                   => [$visitor, '/^#[0-9a-fA-F]{6}$/', [PsalmType::String], [PsalmType::NonEmptyString]],
            'email'                   => [$visitor, '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/', [PsalmType::String], [PsalmType::NonEmptyString]],
            'month'                   => [$visitor, '/^[0-9]{4}\-(0[1-9]|1[012])$/', [PsalmType::String], [PsalmType::NonEmptyString]],
            'month-select'            => [$visitor, '/^[0-9]{4}\-(0?[1-9]|1[012])$/', [PsalmType::String], [PsalmType::NonEmptyString]],
            'number int'              => [$visitor, '(^-?\d*(\.\d+)?$)', [PsalmType::Int], [PsalmType::Int]],
            'number string'           => [$visitor, '(^-?\d*(\.\d+)?$)', [PsalmType::String], [PsalmType::NumericString]],
            'number non-empty-string' => [$visitor, '(^-?\d*(\.\d+)?$)', [PsalmType::NonEmptyString], [PsalmType::NumericString]],
            'tel'                     => [$visitor, "/^[^\r\n]*$/", [PsalmType::String], [PsalmType::String]],
            'tel non-empty-string'    => [$visitor, "/^[^\r\n]*$/", [PsalmType::NonEmptyString], [PsalmType::NonEmptyString]],
            'week'                    => [$visitor, '/^[0-9]{4}\-W[0-9]{2}$/', [PsalmType::String], [PsalmType::NonEmptyString]],
        ];
        // phpcs:enable
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
     * @return FormCliConfigurationArray
     */
    private static function getConfig(): array
    {
        return (new ConfigProvider())();
    }

    private static function getContainer(): ContainerInterface
    {
        return include __DIR__ . '/container.php';
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
