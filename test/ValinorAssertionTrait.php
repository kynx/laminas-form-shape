<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function sprintf;

/**
 * @psalm-require-extends TestCase
 */
trait ValinorAssertionTrait
{
    protected static function assertValinorValidates(bool $expected, string $type, mixed $data): void
    {
        try {
            /** @var mixed $actual */
            $actual = (new MapperBuilder())->mapper()->map($type, $data);
            self::assertTrue($expected, sprintf(
                "Data '%s' should not match type '%s'",
                json_encode($data),
                $type
            ));
            self::assertSame($data, $actual);
        } catch (MappingError $e) {
            self::assertFalse($expected, sprintf(
                "Data '%s' should match type '%s': %s",
                json_encode($data),
                $type,
                $e->getMessage()
            ));
        }
    }
}
