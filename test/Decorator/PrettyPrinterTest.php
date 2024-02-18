<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\DecoratorException;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TCallable;
use Psalm\Type\Atomic\TClassString;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;
use stdClass;

use function array_map;
use function range;

#[CoversClass(PrettyPrinter::class)]
final class PrettyPrinterTest extends TestCase
{
    private PrettyPrinter $decorator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decorator = new PrettyPrinter();
    }

    public function testDecorateEmptyUnionThrowsException(): void
    {
        $expected = DecoratorException::fromEmptyUnion()->getMessage();
        $union    = TypeUtil::getEmptyUnion();

        self::expectException(DecoratorException::class);
        self::expectExceptionMessage($expected);
        $this->decorator->decorate($union);
    }

    public function testDecorateReturnsUnion(): void
    {
        $expected = 'float|int';
        $union    = new Union([new TFloat(), new TInt()]);

        $actual = $this->decorator->decorate($union);
        self::assertSame($expected, $actual);
    }

    public function testDecorateReturnArray(): void
    {
        $expected = 'array<int, string>';
        $union    = new Union([new TArray([new Union([new TInt()]), new Union([new TString()])])]);

        $actual = $this->decorator->decorate($union);
        self::assertSame($expected, $actual);
    }

    public function testDecorateReturnsKeyedArray(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: int,
        }
        END_OF_EXPECTED;
        $union    = new Union([new TKeyedArray(['foo' => new Union([new TInt()])])]);

        $actual = $this->decorator->decorate($union);
        self::assertSame($expected, $actual);
    }

    public function testDecorateIndentsArrayWithKeyedArray(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: array<array-key, array{
                bar: int,
            }>,
        }
        END_OF_EXPECTED;
        $union    = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([
                        Type::getArrayKey(),
                        new Union([
                            new TKeyedArray([
                                'bar' => new Union([new TInt()]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        $actual = $this->decorator->decorate($union);
        self::assertSame($expected, $actual);
    }

    public function testDecorateSortsTypes(): void
    {
        $expected = "'1'|'b'|1|float|null";
        $union    = new Union([
            new TNull(),
            new TLiteralFloat(1.23),
            new TLiteralInt(1),
            TypeUtil::getAtomicStringFromLiteral('b'),
            TypeUtil::getAtomicStringFromLiteral('1'),
        ]);

        $actual = $this->decorator->decorate($union);
        self::assertSame($expected, $actual);
    }

    #[DataProvider('typeProvider')]
    public function testDecorateHandleType(Atomic $type, string $expected): void
    {
        $actual = $this->decorator->decorate(new Union([$type]));
        self::assertSame($expected, $actual);
    }

    public static function typeProvider(): array
    {
        ConfigLoader::load();

        return [
            'scalar'           => [new TScalar(), 'scalar'],
            'bool'             => [new TBool(), 'bool'],
            'callable'         => [new TCallable(), 'callable'],
            'class-string'     => [new TClassString(), 'class-string'],
            'closure'          => [new TClosure(), 'callable'],
            'false'            => [new TFalse(), 'false'],
            'true'             => [new TTrue(), 'true'],
            'int'              => [new TInt(), 'int'],
            'negative-int'     => [new TIntRange(null, -1), 'negative-int'],
            'positive-int'     => [new TIntRange(1, null), 'positive-int'],
            'named object'     => [new TNamedObject(stdClass::class), "stdClass"],
            'non-empty-string' => [new TNonEmptyString(), 'non-empty-string'],
            'numeric-string'   => [new TNumericString(), 'numeric-string'],
        ];
    }

    /**
     * @param non-empty-array<Atomic> $types
     */
    #[DataProvider('combineTypesProvider')]
    public function testDecorateCombinesTypes(array $types, string $expected): void
    {
        $actual = $this->decorator->decorate(new Union($types));
        self::assertSame($expected, $actual);
    }

    public static function combineTypesProvider(): array
    {
        ConfigLoader::load();

        return [
            'true, false'            => [[new TTrue(), new TFalse()], 'bool'],
            'literal int, int'       => [[new TLiteralInt(3), new TInt()], 'int'],
            'int, literal int'       => [[new TInt(), new TLiteralInt(3)], 'int'],
            'int, int range'         => [[new TInt(), new TIntRange(1, null)], 'int'],
            'literal float, float'   => [[new TLiteralFloat(1.23), new TFloat()], 'float'],
            'float, literal float'   => [[new TFloat(), new TLiteralFloat(1.23)], 'float'],
            'literal string, string' => [[TypeUtil::getAtomicStringFromLiteral('foo'), new TString()], 'string'],
            'string, literal string' => [[new TString(), TypeUtil::getAtomicStringFromLiteral('foo')], 'string'],
            'numeric-string, string' => [[new TNumericString(), new TString()], 'string'],
            'string, numeric-string' => [[new TString(), new TNumericString()], 'string'],

            // This behaviour is inconsistent, but can only be fixed upstream: when a TNumericString is added to a union
            // it overrides any other string type! (Um... think TypeUtil::narrow() handles this case now :)
            'non-empty-string, string' => [[new TNonEmptyString(), new TString()], 'string'],
            'string, non-empty-string' => [[new TString(), new TNonEmptyString()], 'non-empty-string'],
        ];
    }

    public function testDecorateLimitsLiterals(): void
    {
        $expected  = 'string';
        $types     = array_map(
            static fn (int $i): TString => TypeUtil::getAtomicStringFromLiteral((string) $i),
            range(1, 4)
        );
        $union     = new Union($types);
        $decorator = new PrettyPrinter('', 3);

        $actual = $decorator->decorate($union);
        self::assertSame($expected, $actual);
    }
}
