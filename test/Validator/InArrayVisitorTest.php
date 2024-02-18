<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\Validator\InArrayVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(InArrayVisitor::class)]
final class InArrayVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        ConfigLoader::load();

        return [
            'empty haystack' => [
                new InArray(['haystack' => []]),
                [new TBool()],
                [new TBool()],
            ],
            'strict null'    => [
                new InArray(['haystack' => [null], 'strict' => true]),
                [new TString(), new TNull()],
                [new TNull()],
            ],
            'loose null'     => [
                new InArray(['haystack' => [null], 'strict' => false]),
                [new TString(), new TNull()],
                [TypeUtil::getAtomicStringFromLiteral(''), new TNull()],
            ],
            'literal string' => [
                new InArray(['haystack' => ['foo'], 'strict' => true]),
                [new TString()],
                [TypeUtil::getAtomicStringFromLiteral("foo")],
            ],
            'loose string'
                => [
                    new InArray(['haystack' => ['foo'], 'strict' => false]),
                    [new TString()],
                    [TypeUtil::getAtomicStringFromLiteral('foo')],
                ],
            'strict int' => [
                new InArray(['haystack' => [123], 'strict' => true]),
                [new TInt(), new TString()],
                [new TLiteralInt(123)],
            ],
            'loose int'
                => [
                    new InArray(['haystack' => [123], 'strict' => false]),
                    [new TInt(), new TString()],
                    [new TLiteralInt(123), TypeUtil::getAtomicStringFromLiteral("123")],
                ],
            'strict float' => [
                new InArray(['haystack' => [1.23], 'strict' => true]),
                [new TFloat()],
                [new TLiteralFloat(1.23)],
            ],
            'loose float'
                => [
                    new InArray(['haystack' => [1.23], 'strict' => false]),
                    [new TFloat(), new TString()],
                    [new TLiteralFloat(1.23), TypeUtil::getAtomicStringFromLiteral('1.23')],
                ],
        ];
    }

    public function testVisitDisallowsEmptyHaystack(): void
    {
        $existing = new Union([new TString()]);
        $builder  = $existing->getBuilder();
        $builder->removeType('string');
        $expected = $builder->freeze();

        $visitor   = new InArrayVisitor(false);
        $validator = new InArray(['haystack' => []]);
        $actual    = $visitor->visit($validator, new Union([new TString()]));
        self::assertEquals($expected, $actual);
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new InArrayVisitor();
    }
}
