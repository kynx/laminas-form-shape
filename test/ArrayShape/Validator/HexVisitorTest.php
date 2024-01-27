<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\HexVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Hex;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(HexVisitor::class)]
final class HexVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new HexVisitor();
        $actual  = $visitor->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Hex(), [PsalmType::String, PsalmType::Null], [PsalmType::NonEmptyString]],
            'int'     => [new Hex(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
        ];
    }
}
