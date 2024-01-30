<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\Literal;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(DigitsVisitor::class)]
final class DigitsVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new DigitsVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Digits(), [PsalmType::String, PsalmType::Null], [PsalmType::NumericString]],
            'int'     => [new Digits(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'float'   => [new Digits(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
            'literal' => [new Digits(), [new Literal([123])], [new Literal([123])]],
        ];
    }
}
