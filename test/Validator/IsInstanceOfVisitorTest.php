<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\IsInstanceOfVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsInstanceOf;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TNamedObject;
use stdClass;

#[CoversClass(IsInstanceOfVisitor::class)]
final class IsInstanceOfVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'instanceof'     => [
                new IsInstanceOf(['className' => stdClass::class]),
                [new TNamedObject(stdClass::class)],
                [new TNamedObject(stdClass::class)],
            ],
            'not instanceof' => [
                new IsInstanceOf(['className' => stdClass::class]),
                [new TNamedObject(self::class)],
                [],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new IsInstanceOfVisitor();
    }
}
