<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\ToNullVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToNull;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;

#[CoversClass(ToNullVisitor::class)]
final class ToNullVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        ConfigLoader::load();

        return [
            'invalid'     => [
                new Boolean(),
                [new TInt()],
                [new TInt()],
            ],
            'zero'        => [
                new ToNull(['type' => 0]),
                [new TString()],
                [new TString()],
            ],
            'none'        => [
                new ToNull(),
                [new TString()],
                [new TNonEmptyString(), new TNull()],
            ],
            'float'       => [
                new ToNull(['type' => ToNull::TYPE_FLOAT]),
                [new TLiteralFloat(0)],
                [new TFloat(), new TNull()],
            ],
            'zero string' => [
                new ToNull(['type' => ToNull::TYPE_ZERO_STRING]),
                [TypeUtil::getAtomicStringFromLiteral('0')],
                [new TString(), new TNull()],
            ],
            'string'      => [
                new ToNull(['type' => ToNull::TYPE_STRING]),
                [new TString()],
                [new TNonEmptyString(), new TNull()],
            ],
            'empty array' => [
                new ToNull(['type' => ToNull::TYPE_EMPTY_ARRAY]),
                [Type::getEmptyArrayAtomic()],
                [new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()]), new TNull()],
            ],
            'int'         => [
                new ToNull(['type' => ToNull::TYPE_INTEGER]),
                [new TLiteralInt(0)],
                [new TInt(), new TNull()],
            ],
            'bool'        => [
                new ToNull(['type' => ToNull::TYPE_BOOLEAN]),
                [new TBool()],
                [new TTrue(), new TNull()],
            ],
            'all'         => [
                new ToNull(['type' => ToNull::TYPE_ALL]),
                [new TString()],
                [new TNonEmptyString(), new TNull()],
            ],
        ];
    }

    protected function getVisitor(): ToNullVisitor
    {
        return new ToNullVisitor();
    }
}
