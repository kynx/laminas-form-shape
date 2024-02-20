<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_splice;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function preg_match;
use function str_starts_with;
use function trim;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FileWriter
{
    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    public static function write(ReflectionClass $reflection, DocBlock $classDocBlock, ?DocBlock $getDataDocBlock): void
    {
        $contents = file_get_contents($reflection->getFileName());

        if ($getDataDocBlock !== null) {
            $contents = self::updateGetDataDocBlock($reflection, $contents, $getDataDocBlock);
        }
        $contents = self::updateClassDocBlock($reflection, $contents, $classDocBlock);

        file_put_contents($reflection->getFileName(), $contents);
    }

    private static function updateClassDocBlock(
        ReflectionClass $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        if ($reflection->getDocComment() === false) {
            return self::addDocBlock($reflection, $contents, $docBlock);
        }

        return self::replaceDocBlock($reflection, $contents, $docBlock);
    }

    private static function updateGetDataDocBlock(
        ReflectionClass $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        if (! $reflection->hasMethod('getData')) {
            return $contents;
        }
        $method = $reflection->getMethod('getData');
        if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
            return $contents;
        }

        if ($method->getDocComment() === false) {
            return self::addDocBlock($method, $contents, $docBlock);
        }

        return self::replaceDocBlock($method, $contents, $docBlock);
    }

    private static function addDocBlock(
        ReflectionClass|ReflectionMethod $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        $startLine = $reflection->getStartLine() - 1;

        $eol    = Eol::detect($contents);
        $lines  = explode($eol, $contents);
        $indent = self::getIndent($lines[$startLine]);

        for ($start = $startLine; $start > 0; $start--) {
            $line = trim($lines[$start - 1]);
            if (! str_starts_with($line, '#')) {
                break;
            }
        }

        array_splice($lines, $start, 0, self::formatDocBlock($docBlock, $indent));

        return implode($eol, $lines);
    }

    private static function replaceDocBlock(
        ReflectionClass|ReflectionMethod $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        $startLine = $reflection->getStartLine() - 1;

        $eol    = Eol::detect($contents);
        $lines  = explode($eol, $contents);
        $indent = self::getIndent($lines[$startLine]);

        $end = $startLine;
        for ($start = $startLine; $start > 0; $start--) {
            $line = trim($lines[$start]);
            if ($line === '*/') {
                $end = $start + 1;
            }
            if ($line === '/**') {
                break;
            }
        }

        array_splice($lines, $start, $end - $start, self::formatDocBlock($docBlock, $indent));

        return implode($eol, $lines);
    }

    /**
     * @return list<string>
     */
    private static function formatDocBlock(DocBlock $docBlock, string $indent): array
    {
        $lines = (string) $docBlock;
        if ($lines === '') {
            return [];
        }

        return array_map(
            static fn (string $line): string => $indent . $line,
            explode("\n", $lines)
        );
    }

    private static function getIndent(string $line): string
    {
        preg_match('/^\s*/', $line, $matches);
        return $matches[0] ?? '';
    }
}
