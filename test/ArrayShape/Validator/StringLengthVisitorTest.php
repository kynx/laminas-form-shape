<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringLengthVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(StringLengthVisitor::class)]
final class StringLengthVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new StringLengthVisitor();
        $actual  = $visitor->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
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
