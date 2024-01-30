<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\BetweenVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(BetweenVisitor::class)]
final class BetweenVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new BetweenVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function visitProvider(): array
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