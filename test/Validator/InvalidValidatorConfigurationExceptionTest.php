<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\InvalidValidatorConfigurationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use function sprintf;

#[CoversClass(InvalidValidatorConfigurationException::class)]
final class InvalidValidatorConfigurationExceptionTest extends TestCase
{
    public function testForVisitorFormatsMessage(): void
    {
        $expected  = sprintf(
            "Invalid configuration for validator-visitors: expected class-string<ValidatorVisitorInterface>, got '%s'",
            stdClass::class,
        );
        $exception = InvalidValidatorConfigurationException::forVisitor(stdClass::class);
        self::assertSame($expected, $exception->getMessage());
    }

    public function testForRegexFormatsMessage(): void
    {
        $expected = sprintf(
            "Invalid configuration for regex.patterns: expected class-string<Atomic>, got %s",
            stdClass::class
        );

        $exception = InvalidValidatorConfigurationException::forRegex(stdClass::class);
        self::assertSame($expected, $exception->getMessage());
    }
}
