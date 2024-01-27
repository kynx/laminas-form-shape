<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(ExplodeVisitor::class)]
final class ExplodeVisitorTest extends TestCase
{
    /**
     * @param list<PsalmType> $itemTypes
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(
        ValidatorInterface $validator,
        array $itemTypes,
        array $existing,
        array $expected
    ): void {
        $visitor = new ExplodeVisitor([new DigitsVisitor()], $itemTypes);
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function visitProvider(): array
    {
        $digits      = new Digits();
        $validator   = new Explode(['validator' => $digits]);
        $noDelimiter = new Explode(['validator' => $digits, 'valueDelimiter' => null]);
        $traversable = new ClassString(Traversable::class);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'             => [new Barcode(), [PsalmType::String], [PsalmType::Bool], [PsalmType::Bool]],
            'no validator'        => [new Explode(), [PsalmType::Bool], [PsalmType::Int], [PsalmType::Int]],
            'not explodeable'     => [$noDelimiter, [PsalmType::String], [PsalmType::String], [PsalmType::NumericString]],
            'array'               => [$validator, [PsalmType::Bool], [PsalmType::Array], [new Generic(PsalmType::Array, [])]],
            'numeric array'       => [$validator, [PsalmType::String], [PsalmType::Array], [new Generic(PsalmType::Array, [PsalmType::NumericString])]],
            'traversable'         => [$validator, [PsalmType::Bool], [$traversable], [new Generic($traversable, [])]],
            'numeric traversable' => [$validator, [PsalmType::String], [$traversable], [new Generic($traversable, [PsalmType::NumericString])]],
            'string'              => [$validator, [PsalmType::String], [PsalmType::String], [PsalmType::String, PsalmType::NumericString]],
            'generic class'       => [$validator, [PsalmType::String], [new Generic(new ClassString(stdClass::class), [])], []],
            'mixed'               => [$validator, [PsalmType::Bool], [PsalmType::Array, PsalmType::Int], [new Generic(PsalmType::Array, []), PsalmType::Int]],
        ];
        // phpcs:enable
    }
}
