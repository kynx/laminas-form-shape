<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsbnVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Isbn;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(IsbnVisitor::class)]
final class IsbnVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new IsbnVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'         => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'int'             => [new Isbn(), [PsalmType::Bool, PsalmType::Int], [PsalmType::Int]],
            'positive int'    => [new Isbn(), [PsalmType::Bool, PsalmType::PositiveInt], [PsalmType::PositiveInt]],
            'negative int'    => [new Isbn(), [PsalmType::Bool, PsalmType::NegativeInt], []],
            'string'          => [new Isbn(), [PsalmType::Bool, PsalmType::String], [PsalmType::String]],
            'nonempty string' => [new Isbn(), [PsalmType::Bool, PsalmType::NonEmptyString], [PsalmType::NonEmptyString]],
        ];
        // phpcs:eanble
    }
}
