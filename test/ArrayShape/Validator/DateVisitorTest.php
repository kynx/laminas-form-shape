<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DateVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Date;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(DateVisitor::class)]
final class DateVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new DateVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        $dateTime          = new ClassString(DateTime::class);
        $dateTimeImmutable = new ClassString(DateTimeImmutable::class);

        return [
            'invalid'            => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'datetime'           => [new Date(), [$dateTime, PsalmType::Null], [$dateTime]],
            'datetime immutable' => [new Date(), [$dateTimeImmutable, PsalmType::Null], [$dateTimeImmutable]],
            'float'              => [new Date(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
            'int'                => [new Date(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'negative int'       => [new Date(), [PsalmType::NegativeInt, PsalmType::Null], [PsalmType::NegativeInt]],
            'array'              => [new Date(), [PsalmType::Array, PsalmType::Null], [PsalmType::NonEmptyArray]],
            'string'             => [new Date(), [PsalmType::String, PsalmType::Null], [PsalmType::NonEmptyString]],
            'positive int'       => [new Date(), [PsalmType::PositiveInt, PsalmType::Null], [PsalmType::PositiveInt]],
        ];
    }
}
