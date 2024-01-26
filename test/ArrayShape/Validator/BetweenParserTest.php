<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\BetweenParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\BetweenParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class BetweenParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new BetweenParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertEquals($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
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
