<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\ElementShapeDecorator;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElementShapeDecorator::class)]
final class ElementShapeDecoratorTest extends TestCase
{
    #[DataProvider('decorateProvider')]
    public function testDecorate(ElementShape $type, string $expected): void
    {
        $decorator = new ElementShapeDecorator();
        $actual    = $decorator->decorate($type);
        self::assertSame($expected, $actual);
    }

    public static function decorateProvider(): array
    {
        return [
            'single' => [new ElementShape('foo', [PsalmType::String]), 'string'],
            'sorted' => [new ElementShape('foo', [PsalmType::Int, PsalmType::Float]), 'float|int'],
        ];
    }
}
