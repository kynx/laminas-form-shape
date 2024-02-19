<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use function str_contains;
use function substr_count;

use const PHP_EOL;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class Eol
{
    /**
     * @return non-empty-string
     */
    public static function detectEol(string $contents): string
    {
        if (! str_contains($contents, "\n")) {
            return PHP_EOL;
        }

        return substr_count($contents, "\r\n") >= substr_count($contents, "\n")
            ? "\r\n"
            : "\n";
    }
}
