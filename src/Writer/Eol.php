<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use function str_contains;
use function substr;
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
    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    /**
     * @return non-empty-string
     */
    public static function detect(string $contents): string
    {
        $test = substr($contents, 0, 4096);
        if (! str_contains($test, "\n")) {
            return PHP_EOL;
        }

        return substr_count($test, "\n") > substr_count($test, "\r\n")
            ? "\n"
            : "\r\n";
    }
}
