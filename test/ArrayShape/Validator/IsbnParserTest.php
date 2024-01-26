<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsbnParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Isbn;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\IsbnParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class IsbnParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new IsbnParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
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
