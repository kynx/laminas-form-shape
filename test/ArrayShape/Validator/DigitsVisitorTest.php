<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(DigitsVisitor::class)]
final class DigitsVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new DigitsVisitor();
        $actual  = $visitor->getTypes($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
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
