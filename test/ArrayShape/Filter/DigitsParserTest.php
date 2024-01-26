<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\DigitsParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
#[CoversClass(DigitsParser::class)]
final class DigitsParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(FilterInterface $filter, array $existing, array $expected): void
    {
        $parser = new DigitsParser();
        $actual = $parser->getTypes($filter, $existing);
        self::assertSame($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'   => [new Boolean(), [PsalmType::Int], [PsalmType::Int]],
            'no digits' => [new Digits(), [PsalmType::Bool], [PsalmType::Bool]],
            'int'       => [new Digits(), [PsalmType::Int], [PsalmType::NumericString]],
            'float'     => [new Digits(), [PsalmType::Float], [PsalmType::NumericString]],
            'string'    => [new Digits(), [PsalmType::String], [PsalmType::String]],
            'mixed'     => [new Digits(), [PsalmType::Bool, PsalmType::Int], [PsalmType::Bool, PsalmType::NumericString]],
        ];
        // phpcs:enable
    }
}
