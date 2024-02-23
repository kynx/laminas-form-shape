<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(ArrayInputVisitor::class)]
final class ArrayInputVisitorTest extends TestCase
{
    private ArrayInputVisitor $visitor;

    protected function setUp(): void
    {
        $this->visitor = new ArrayInputVisitor(new InputVisitor([], []));
    }

    public function testVisitInvalidInputReturnsNull(): void
    {
        $actual = $this->visitor->visit(new Input());
        self::assertNull($actual);
    }

    public function testVisitReturnsNonEmptyArray(): void
    {
        $expected = new Union([
            new TNonEmptyArray([Type::getArrayKey(), new Union([new TString(), new TNull()])]),
        ]);
        $input    = new ArrayInput();
        $input->setRequired(true);

        $actual = $this->visitor->visit($input);
        self::assertEquals($expected, $actual);
    }
}
