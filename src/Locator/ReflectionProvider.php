<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use Composer\Autoload\ClassLoader;
use ReflectionClass;
use ReflectionException;

use function array_keys;
use function array_reduce;
use function is_a;
use function realpath;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @template T of object
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class ReflectionProvider
{
    /** @var array<string, string> */
    private array $psr4Namespaces;

    /**
     * @param class-string<T> $implements
     * @param class-string $notInstanceOf
     */
    public function __construct(
        ClassLoader $loader,
        private string $implements,
        private ?string $notInstanceOf = null
    ) {
        $psr4Namespaces = [];
        foreach ($loader->getPrefixesPsr4() as $namespace => $dirs) {
            foreach ($dirs as $dir) {
                $path = realpath($dir);
                if ($path === false) {
                    continue;
                }

                $psr4Namespaces[$path] = $namespace;
            }
        }

        $this->psr4Namespaces = $psr4Namespaces;
    }

    /**
     * @return ReflectionClass<T>|null
     */
    public function getReflection(string $path): ?ReflectionClass
    {
        if (! str_ends_with($path, '.php')) {
            return null;
        }

        if (false === ($realPath = realpath($path))) {
            return null;
        }

        /** @var null|class-string $className */
        $className = $this->resolvePsr4Class($realPath);
        if ($className === null) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException) {
            return null;
        }

        if ($this->notInstanceOf !== null && is_a($reflection->getName(), $this->notInstanceOf, true)) {
            return null;
        }

        if ($reflection->implementsInterface($this->implements)) {
            // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
            /** @var ReflectionClass<T> $reflection */
            return $reflection;
        }

        return null;
    }

    private function resolvePsr4Class(string $path): ?string
    {
        $dir = array_reduce(
            array_keys($this->psr4Namespaces),
            static fn (?string $found, string $dir): ?string => str_starts_with($path, $dir) ? $dir : $found
        );
        if ($dir === null) {
            return null;
        }

        return $this->psr4Namespaces[$dir]
            . str_replace(
                DIRECTORY_SEPARATOR,
                '\\',
                substr($path, strlen($dir) + 1, -4)
            );
    }
}
