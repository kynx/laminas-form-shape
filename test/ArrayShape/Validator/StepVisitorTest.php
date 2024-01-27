<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StepVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Step;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(StepVisitor::class)]
final class StepVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new StepVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Step(), [PsalmType::String], []],
            'numbers' => [new Step(), [PsalmType::Bool, PsalmType::Int, PsalmType::Float], [PsalmType::Int, PsalmType::Float]],
        ];
        // phpcs:enable
    }
}
