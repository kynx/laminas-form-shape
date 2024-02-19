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
    public function write(ReflectionClass $reflection, DocBlock $classDocBlock, ?DocBlock $getDataDocBlock): void
    {
        $contents = file_get_contents($reflection->getFileName());

        $contents = $this->updateClassDocBlock($reflection, $contents, $classDocBlock);
        if ($getDataDocBlock !== null) {
            $contents = $this->updateGetDataDocBlock($reflection, $contents, $getDataDocBlock);
        }

        file_put_contents($reflection->getFileName(), $contents);
    }

    private function updateClassDocBlock(ReflectionClass $reflection, string $contents, DocBlock $docBlock): string
    {
        if ($reflection->getDocComment() === false) {
            return $this->addDocBlock($reflection, $contents, $docBlock);
        }

        return $this->replaceDocBlock($reflection, $contents, $docBlock);
    }

    private function updateGetDataDocBlock(ReflectionClass $reflection, string $contents, DocBlock $docBlock): string
    {
        if (! $reflection->hasMethod('getData')) {
            return $contents;
        }
        $method = $reflection->getMethod('getData');
        if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
            return $contents;
        }

        if ($method->getDocComment() === false) {
            return $this->addDocBlock($method, $contents, $docBlock);
        }

        return $this->replaceDocBlock($method, $contents, $docBlock);
    }

    private function addDocBlock(
        ReflectionClass|ReflectionMethod $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        $startLine = $reflection->getStartLine() - 1;

        $eol    = Eol::detectEol($contents);
        $lines  = explode($eol, $contents);
        $indent = $this->getIndent($lines[$startLine]);

        for ($start = $startLine; $start > 0; $start--) {
            $line = trim($lines[$start - 1]);
            if (! str_starts_with($line, '#')) {
                break;
            }
        }

        array_splice($lines, $start, 0, $this->formatDocBlock($docBlock, $indent));

        return implode($eol, $lines);
    }

    private function replaceDocBlock(
        ReflectionClass|ReflectionMethod $reflection,
        string $contents,
        DocBlock $docBlock
    ): string {
        $startLine = $reflection->getStartLine() - 1;

        $eol    = Eol::detectEol($contents);
        $lines  = explode($eol, $contents);
        $indent = $this->getIndent($lines[$startLine]);

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

        array_splice($lines, $start, $end - $start, $this->formatDocBlock($docBlock, $indent));

        return implode($eol, $lines);
    }

    /**
     * @return list<string>
     */
    private function formatDocBlock(DocBlock $docBlock, string $indent): array
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

    private function getIndent(string $line): string
    {
        preg_match('/^\s*/', $line, $matches);
        return $matches[0] ?? '';
    }
}
