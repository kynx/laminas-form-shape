<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\KeyedArrayDecorator;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(KeyedArrayDecorator::class)]
final class KeyedArrayDecoratorTest extends TestCase
{
    private KeyedArrayDecorator $decorator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decorator = new KeyedArrayDecorator(new PrettyPrinter());
    }

    #[DataProvider('typeNameProvider')]
    public function testDecorateEscapesName(TKeyedArray $array, string $expected): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            $expected,
        }
        END_OF_EXPECTED;

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, list{TKeyedArray, string}>
     */
    public static function typeNameProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'required' => [new TKeyedArray(['foo' => new Union([new TInt()])]), 'foo: int'],
            'escaped'  => [new TKeyedArray(['foo bar' => new Union([new TInt()])]), "'foo bar': int"],
            'optional' => [new TKeyedArray(['foo' => new Union([new TInt()], ['possibly_undefined' => true])]), 'foo?: int'],
        ];
        // phpcs:enable
    }

    public function testDecoratePadsNames(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo:     float|int,
            barbar?: string,
        }
        END_OF_EXPECTED;
        $array    = new TKeyedArray([
            'foo'    => new Union([new TFloat(), new TInt()]),
            'barbar' => new Union([new TString()], ['possibly_undefined' => true]),
        ]);

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }

    public function testDecorateIndentsSubArrays(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: string,
            bar: array{
                baz: int,
            },
        }
        END_OF_EXPECTED;
        $array    = new TKeyedArray([
            'foo' => new Union([new TString()]),
            'bar' => new Union([
                new TKeyedArray([
                    'baz' => new Union([new TInt()]),
                ]),
            ]),
        ]);

        $actual = $this->decorator->decorate($array);
        self::assertSame($expected, $actual);
    }
}
