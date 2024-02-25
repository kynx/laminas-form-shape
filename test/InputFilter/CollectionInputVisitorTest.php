<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\CollectionInput;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(CollectionInputVisitor::class)]
final class CollectionInputVisitorTest extends TestCase
{
    private CollectionInputVisitor $visitor;

    protected function setUp(): void
    {
        $this->visitor = new CollectionInputVisitor(new InputVisitor([], []));
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
        $input    = CollectionInput::fromInput(new Input(), 42);

        $actual = $this->visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsArray(): void
    {
        $expected = new Union([
            new TArray([Type::getArrayKey(), new Union([new TString(), new TNull()])]),
        ]);
        $input    = CollectionInput::fromInput(new Input(), 0);

        $actual = $this->visitor->visit($input);
        self::assertEquals($expected, $actual);
    }
}
