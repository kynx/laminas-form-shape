<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\NotEmptyParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\NotEmptyParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class NotEmptyParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new NotEmptyParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
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
