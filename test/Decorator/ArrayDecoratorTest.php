<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\ArrayDecorator;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(ArrayDecorator::class)]
final class ArrayDecoratorTest extends TestCase
{
    private ArrayDecorator $decorator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decorator = new ArrayDecorator(new PrettyPrinter());
    }

    public function testDecorateReturnsNonEmptyArray(): void
    {
        $expected = 'non-empty-array<array-key, int>';
        $array    = new TNonEmptyArray([new Union([new TArrayKey()]), new Union([new TInt()])]);

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }

    public function testDecorateReturnsStandardArray(): void
    {
        $expected = 'array<string, int>';
        $array    = new TArray([new Union([new TString()]), new Union([new TInt()])]);

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }

    public function testDecorateNestedKeyedArray(): void
    {
        $expected = <<<END_OF_EXPECTED
        array<int, array{
            foo: int,
        }>
        END_OF_EXPECTED;
        $nested   = new TKeyedArray(['foo' => new Union([new TInt()])]);
        $array    = new TArray([new Union([new TInt()]), new Union([$nested])]);

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }
}
