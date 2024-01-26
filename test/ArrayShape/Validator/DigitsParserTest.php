<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class DigitsParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new DigitsParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Digits(), [PsalmType::String, PsalmType::Null], [PsalmType::NumericString]],
            'int'     => [new Digits(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'float'   => [new Digits(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
        ];
    }
}
