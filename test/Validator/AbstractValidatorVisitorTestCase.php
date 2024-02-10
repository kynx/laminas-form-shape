<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNever;
use Psalm\Type\Union;

use function array_values;

abstract class AbstractValidatorVisitorTestCase extends TestCase
{
    /**
     * @return non-empty-array<list{ValidatorInterface, non-empty-array<Atomic>, array<Atomic>}>
     */
    abstract protected static function visitProvider(): array;

    abstract protected static function getValidatorVisitor(): ValidatorVisitorInterface;

    /**
     * @param non-empty-array<Atomic> $existing
     * @param array<Atomic> $expected
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor   = $this->getValidatorVisitor();
        $validated = $visitor->visit($validator, new Union($existing));
        $actual    = array_values($validated->getAtomicTypes());
        self::assertEquals($expected, $actual);
    }

    public function testValidateIgnoresUnmatchedValidator(): void
    {
        $expected  = new Union([new TNever()]);
        $validator = new class implements ValidatorInterface {
            /** @param mixed $value */
            public function isValid($value): bool
            {
                return false;
            }

            public function getMessages(): array
            {
                return [];
            }
        };

        $actual = $this->getValidatorVisitor()->visit($validator, $expected);
        self::assertSame($expected, $actual);
    }
}
