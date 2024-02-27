<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormShape\Validator\InArrayVisitor;
use KynxTest\Laminas\FormShape\Psalm\GetIdVisitor;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Union;

#[CoversClass(ArrayInputVisitor::class)]
final class ArrayInputVisitorTest extends TestCase
{
    public function testVisitNonArrayInputReturnsNull(): void
    {
        $visitor = new ArrayInputVisitor([], []);
        $actual  = $visitor->visit(new Input());
        self::assertNull($actual);
    }

    public function testVisitImpossibleInputThrowsException(): void
    {
        $input = new ArrayInput('foo');
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new ArrayInputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        self::expectException(InputVisitorException::class);
        self::expectExceptionMessage("Cannot get type for 'foo'");
        $visitor->visit($input);
    }

    public function testVisitReturnsArrayOfValidatedType(): void
    {
        $expected = new Union([
            new TArray([
                Type::getArrayKey(),
                new Union([new TNumericString(), new TInt()]),
            ]),
        ]);
        $input    = new ArrayInput();
        $input->setRequired(false);
        $input->setContinueIfEmpty(true);
        $input->getFilterChain()->attach(new ToInt());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new ArrayInputVisitor([new ToIntVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsNonEmptyArray(): void
    {
        $expected = new Union([
            new TNonEmptyArray([
                Type::getArrayKey(),
                new Union([new TNumericString(), new TInt()]),
            ]),
        ]);
        $input    = new ArrayInput();
        $input->getFilterChain()->attach(new ToInt());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new ArrayInputVisitor([new ToIntVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsExplodeArray(): void
    {
        ConfigLoader::load();

        $array = new TNonEmptyArray([
            Type::getArrayKey(),
            new Union([
                TypeUtil::getAtomicStringFromLiteral('1'),
                TypeUtil::getAtomicStringFromLiteral('2'),
            ]),
        ]);
        (new GetIdVisitor())->traverse($array);

        // This is what a real live MultiCheckbox actually can return!
        $expected = new Union([
            $array,
            TypeUtil::getAtomicStringFromLiteral('1'),
            TypeUtil::getAtomicStringFromLiteral('2'),
        ]);

        $input   = new ArrayInput();
        $inArray = new InArray(['haystack' => ['1', '2'], 'strict' => true]);
        $explode = new Explode(['valueDelimiter' => null, 'validator' => $inArray]);
        $input->getValidatorChain()->attach($explode);
        $visitor = new ArrayInputVisitor([], [new ExplodeVisitor([new InArrayVisitor()])]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitAddsFallbackValue(): void
    {
        ConfigLoader::load();

        $array    = new TNonEmptyArray([
            Type::getArrayKey(),
            new Union([
                TypeUtil::getAtomicStringFromLiteral('1'),
                TypeUtil::getAtomicStringFromLiteral('2'),
                TypeUtil::getAtomicStringFromLiteral('a'),
            ]),
        ]);
        $expected = new Union([
            $array,
            TypeUtil::getAtomicStringFromLiteral('1'),
            TypeUtil::getAtomicStringFromLiteral('2'),
        ]);

        $input   = new ArrayInput();
        $inArray = new InArray(['haystack' => ['1', '2'], 'strict' => true]);
        $explode = new Explode(['valueDelimiter' => null, 'validator' => $inArray]);
        $input->getValidatorChain()->attach($explode);
        $input->setFallbackValue(['a']);
        $visitor = new ArrayInputVisitor([], [new ExplodeVisitor([new InArrayVisitor()])]);

        $actual = $visitor->visit($input);

        self::assertNotNull($actual);
        self::assertEquals($expected, $actual);

        $decorated = (new PrettyPrinter())->decorate($actual);
        self::assertSame("'1'|'2'|non-empty-array<array-key, '1'|'2'|'a'>", $decorated);
    }
}
