<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\InputShapeDecorator;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputShapeDecorator::class)]
final class InputShapeDecoratorTest extends TestCase
{
    #[DataProvider('decorateProvider')]
    public function testDecorate(InputShape $type, string $expected): void
    {
        $decorator = new InputShapeDecorator();
        $actual    = $decorator->decorate($type);
        self::assertSame($expected, $actual);
    }

    public static function decorateProvider(): array
    {
        return [
            'single' => [new InputShape('foo', [PsalmType::String]), 'string'],
            'sorted' => [new InputShape('foo', [PsalmType::Int, PsalmType::Float]), 'float|int'],
        ];
    }
}
