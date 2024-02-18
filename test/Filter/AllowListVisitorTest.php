<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;

#[CoversClass(AllowListVisitor::class)]
final class AllowListVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        ConfigLoader::load();

        return [
            'invalid'                 => [
                new Boolean(),
                [new TInt()],
                [new TInt()],
            ],
            'empty list'              => [
                new AllowList(['list' => []]),
                [new TBool()],
                [new TBool(), new TNull()],
            ],
            'strict list'             => [
                new AllowList(['list' => [123, true], 'strict' => true]),
                [new TInt(), new TBool()],
                [new TLiteralInt(123), new TTrue(), new TNull()],
            ],
            'lax list'                => [
                new AllowList(['list' => [123], 'strict' => false]),
                [new TString()],
                [TypeUtil::getAtomicStringFromLiteral('123'), new TNull()],
            ],
            'strict narrows previous' => [
                new AllowList(['list' => ['foo', 123], 'strict' => true]),
                [new TInt(), new TNumericString()],
                [new TLiteralInt(123), new TNull()],
            ],
            'lax narrows previous'    => [
                new AllowList(['list' => [123, true], 'strict' => false]),
                [new TString()],
                [new TNonEmptyString(), new TNull()],
            ],
        ];
    }

    public function testVisitDisallowsEmptyList(): void
    {
        $expected = new Union([new TNull()]);
        $visitor  = new AllowListVisitor(false);
        $filter   = new AllowList(['list' => []]);
        $actual   = $visitor->visit($filter, new Union([new TString()]));
        self::assertEquals($expected, $actual);
    }

    protected function getVisitor(): AllowListVisitor
    {
        return new AllowListVisitor();
    }
}
