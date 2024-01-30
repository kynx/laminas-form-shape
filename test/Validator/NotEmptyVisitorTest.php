<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(NotEmptyVisitor::class)]
final class NotEmptyVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new NotEmptyVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'       => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'object count'  => [new NotEmpty(NotEmpty::OBJECT_COUNT), [PsalmType::Object], [PsalmType::Object]],
            'object string' => [new NotEmpty(NotEmpty::OBJECT_STRING), [PsalmType::Object], [PsalmType::Object]],
            'object'        => [new NotEmpty(NotEmpty::OBJECT), [PsalmType::String, PsalmType::Object], [PsalmType::String]],
            'space'         => [new NotEmpty(NotEmpty::SPACE), [PsalmType::String], [PsalmType::NonEmptyString]],
            'null'          => [new NotEmpty(NotEmpty::NULL), [PsalmType::String, PsalmType::Null], [PsalmType::String]],
            'empty array'   => [new NotEmpty(NotEmpty::EMPTY_ARRAY), [PsalmType::Array], [PsalmType::NonEmptyArray]],
            'string'        => [new NotEmpty(NotEmpty::STRING), [PsalmType::String], [PsalmType::NonEmptyString]],
            'int'           => [new NotEmpty(NotEmpty::INTEGER), [PsalmType::Int], [PsalmType::NegativeInt, PsalmType::PositiveInt]],
            'bool'          => [new NotEmpty(NotEmpty::BOOLEAN), [PsalmType::Bool], [PsalmType::True]],
        ];
        // phpcs:enable
    }
}
