<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;

#[CoversClass(BooleanVisitor::class)]
final class BooleanVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        ConfigLoader::load();

        return [
            'invalid filter' => [
                new AllowList(),
                [new TInt()],
                [new TInt()],
            ],
            'casting'        => [
                new Boolean(['casting' => true]),
                [new TInt()],
                [new TBool()],
            ],
            'false string'   => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_FALSE_STRING]),
                [TLiteralString::make('false'), TLiteralString::make('true')],
                [new TBool()],
            ],
            'null'           => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_NULL]),
                [new TString(), new TNull()],
                [new TString(), new TBool()],
            ],
            'empty array'    => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_EMPTY_ARRAY]),
                [Type::getEmptyArrayAtomic()],
                [new TBool()],
            ],
            'zero string'    => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_ZERO_STRING]),
                [TLiteralString::make('0'), TLiteralString::make('1')],
                [new TBool()],
            ],
            'float'          => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_FLOAT]),
                [new TLiteralFloat(0.0), new TLiteralFloat(1.0)],
                [new TBool()],
            ],
            'int'            => [
                new Boolean(['casting' => false, 'type' => Boolean::TYPE_INTEGER]),
                [new TLiteralInt(0), new TLiteralInt(1)],
                [new TBool()],
            ],
        ];
    }

    protected function getVisitor(): BooleanVisitor
    {
        return new BooleanVisitor();
    }
}
