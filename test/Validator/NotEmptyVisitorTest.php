<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Countable;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\NotEmpty;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TObject;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use stdClass;
use Stringable;

#[CoversClass(NotEmptyVisitor::class)]
final class NotEmptyVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        ConfigLoader::load();

        return [
            'object count'   => [
                new NotEmpty(NotEmpty::OBJECT_COUNT),
                [new TObject(), new TNamedObject(stdClass::class)],
                [new TNamedObject(Countable::class)],
            ],
            'object string'  => [
                new NotEmpty(NotEmpty::OBJECT_STRING),
                [new TObject()],
                [new TNamedObject(Stringable::class)],
            ],
            'object'         => [
                new NotEmpty(NotEmpty::OBJECT),
                [new TObject()],
                [new TObject()],
            ],
            'object not set' => [
                new NotEmpty(NotEmpty::ZERO),
                [new TObject(), new TString()],
                [new TString()],
            ],
            'space'          => [
                new NotEmpty(NotEmpty::SPACE),
                [TLiteralString::make(' '), new TInt()],
                [new TInt()],
            ],
            'null'           => [
                new NotEmpty(NotEmpty::NULL),
                [new TString(), new TNull()],
                [new TString()],
            ],
            'empty array'    => [
                new NotEmpty(NotEmpty::EMPTY_ARRAY),
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
                [new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()])],
            ],
            'zero'           => [
                new NotEmpty(NotEmpty::ZERO),
                [TLiteralString::make('0'), new TInt()],
                [new TInt()],
            ],
            'string'         => [
                new NotEmpty(NotEmpty::STRING),
                [TLiteralString::make(''), TLiteralString::make('a')],
                [TLiteralString::make('a')],
            ],
            'float'          => [
                new NotEmpty(NotEmpty::FLOAT),
                [new Type\Atomic\TLiteralFloat(0.0), new TInt()],
                [new TInt()],
            ],
            'int'            => [
                new NotEmpty(NotEmpty::INTEGER),
                [new TInt()],
                [new TIntRange(null, -1), new TIntRange(1, null)],
            ],
            'bool'           => [
                new NotEmpty(NotEmpty::BOOLEAN),
                [new TBool()],
                [new TTrue()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new NotEmptyVisitor();
    }
}
