<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\StringLengthVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(StringLengthVisitor::class)]
final class StringLengthVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new StringLengthVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'    => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'empty'      => [new StringLength(), [PsalmType::Bool], []],
            'zero min'   => [new StringLength(['min' => 0]), [PsalmType::String, PsalmType::Null], [PsalmType::String]],
            'min length' => [new StringLength(['min' => 10]), [PsalmType::String], [PsalmType::NonEmptyString]],
        ];
        // phpcs:enable
    }
}
