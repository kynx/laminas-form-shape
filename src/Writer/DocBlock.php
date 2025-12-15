<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use Stringable;

use function array_map;
use function array_slice;
use function array_splice;
use function array_values;
use function count;
use function explode;
use function implode;
use function preg_replace;
use function rtrim;
use function str_starts_with;
use function trim;

use const PHP_EOL;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class DocBlock implements Stringable
{
    /**
     * @param list<string|TagInterface> $sections
     */
    private function __construct(private array $sections)
    {
    }

    public static function fromDocComment(string|false $docComment): self
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison  */
        $docComment = $docComment ?: <<<EOD
        /**
         */
        EOD;

        $lines = explode(PHP_EOL, trim($docComment));

        $sections = $section = [];
        $inTag    = false;
        foreach (array_slice($lines, 1, -1) as $line) {
            $line = rtrim((string) preg_replace('/^\s*\*? ?/', '', $line));
            if ($inTag && self::isEndOfTag($line)) {
                $sections[] = new GenericTag(implode("\n", $section));
                $section    = [];
                $inTag      = false;
            }
            if (self::isStartOfTag($line)) {
                if ($section !== []) {
                    $sections[] = implode("\n", $section);
                }
                $section = [];
                $inTag   = true;
            }
            $section[] = $line;
        }

        if ($inTag) {
            $sections[] = new GenericTag(implode("\n", $section));
        } elseif ($section !== []) {
            $sections[] = implode("\n", $section);
        }

        return new self($sections);
    }

    public function withTag(TagInterface $tag): self
    {
        $sections = $this->sections;
        if ($sections === []) {
            return new self([$tag]);
        }

        foreach ($sections as $i => $section) {
            if (! $section instanceof TagInterface) {
                continue;
            }

            if ($tag->matches($section)) {
                $sections[$i] = $tag;
                return new self($sections);
            }
        }

        $before = count($sections);
        foreach ($sections as $i => $section) {
            if (! $section instanceof TagInterface) {
                continue;
            }

            if ($tag->isBefore($section)) {
                $before = $i;
                break;
            }
        }

        array_splice($sections, $before, 0, [$tag]);
        return new self($sections);
    }

    public function withoutTag(TagInterface $tag): self
    {
        $sections = $this->sections;
        foreach ($sections as $i => $section) {
            if (! $section instanceof TagInterface) {
                continue;
            }

            if ($tag->matches($section)) {
                unset($sections[$i]);
            }
        }

        return new self(array_values($sections));
    }

    public function getContents(string $eol = "\n"): string
    {
        return implode($eol, array_map(
            fn (string|TagInterface $s): string => implode($eol, explode("\n", (string) $s)),
            $this->sections
        ));
    }

    public function __toString(): string
    {
        if ($this->sections === []) {
            return '';
        }

        return "/**\n"
            . $this->formatSections() . "\n"
            . " */";
    }

    private static function isStartOfTag(string $line): bool
    {
        return str_starts_with(trim($line), '@');
    }

    private static function isEndOfTag(string $line): bool
    {
        return trim($line) === '' || self::isStartOfTag($line);
    }

    private function formatSections(): string
    {
        return ' * ' . $this->implode(array_map(
            fn (string|TagInterface $s): string => $this->implode(explode("\n", (string) $s)),
            $this->sections
        ));
    }

    /**
     * @param array<string> $parts
     */
    private function implode(array $parts): string
    {
        return implode("\n * ", $parts);
    }
}
