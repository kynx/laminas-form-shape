<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\BetweenVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(BetweenVisitor::class)]
final class BetweenVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new BetweenVisitor();
        $actual  = $visitor->getTypes($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid validator' => [new Barcode(), [PsalmType::Int], [PsalmType::Int]],
            'numeric'           => [new Between(['min' => 0, 'max' => 1]), [PsalmType::Int, PsalmType::Float, PsalmType::Null], [PsalmType::Int, PsalmType::Float]],
            'numeric string'    => [new Between(['min' => 0, 'max' => 1]), [PsalmType::String, PsalmType::Null], [PsalmType::NumericString]],
            'string'            => [new Between(['min' => 'a', 'max' => 'm']), [PsalmType::String, PsalmType::Null], [PsalmType::String]],
        ];
        // phpcs:enable
    }
}
