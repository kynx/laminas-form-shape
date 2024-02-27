<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Laminas\Filter\Boolean;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Union;

#[CoversClass(InputVisitor::class)]
final class InputVisitorTest extends TestCase
{
    public function testVisitAddsFallback(): void
    {
        $expected = new Union([new TLiteralFloat(1.23)]);
        $input    = new Input('foo');
        $input->setFallbackValue(1.23);
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);

        self::assertEquals($expected, $actual);
    }

    public function testVisitEmptyTypesThrowsException(): void
    {
        $input = new Input('foo');
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        self::expectException(InputVisitorException::class);
        self::expectExceptionMessage("Cannot get type for 'foo'");
        $visitor->visit($input);
    }
}
