<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DateStepVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\DateStep;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(DateStepVisitor::class)]
final class DateStepVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new DateStepVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        $dateTime          = new ClassString(DateTime::class);
        $dateTimeImmutable = new ClassString(DateTimeImmutable::class);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'            => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'datetime'           => [new DateStep(), [$dateTime, PsalmType::Null], [$dateTime]],
            'datetime immutable' => [new DateStep(), [$dateTimeImmutable, PsalmType::Null], [$dateTimeImmutable]],
            'float'              => [new DateStep(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
            'int'                => [new DateStep(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'negative int'       => [new DateStep(), [PsalmType::NegativeInt, PsalmType::Null], [PsalmType::NegativeInt]],
            'array'              => [new DateStep(), [PsalmType::Array, PsalmType::Null], [PsalmType::NonEmptyArray]],
            'string'             => [new DateStep(), [PsalmType::String, PsalmType::Null], [PsalmType::NonEmptyString]],
            'positive int'       => [new DateStep(), [PsalmType::PositiveInt, PsalmType::Null], [PsalmType::PositiveInt]],
        ];
        // phpcs:enable
    }
}
