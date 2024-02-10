<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use ArrayIterator;
use Kynx\Laminas\FormShape\Validator\IsCountableVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsCountable;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(IsCountableVisitor::class)]
final class IsCountableVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'array'       => [
                new IsCountable(),
                [new TArray([Type::getArrayKey(), Type::getMixed()]), new TString()],
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
            ],
            'keyed array' => [
                new IsCountable(),
                [new TKeyedArray(['a' => new Union([new TString()])])],
                [new TKeyedArray(['a' => new Union([new TString()])])],
            ],
            'countable'   => [
                new IsCountable(),
                [new TNamedObject(ArrayIterator::class)],
                [new TNamedObject(ArrayIterator::class)],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new IsCountableVisitor();
    }
}
