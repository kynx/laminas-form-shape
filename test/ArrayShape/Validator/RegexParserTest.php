<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexPattern;
use Laminas\Validator\Barcode;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\RegexParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class RegexParserTest extends TestCase
{
    private const INT           = '/^\d+$/';
    private const NO_UNDERSCORE = '/^[^_]*$/';
    private const DATE          = '/^\d\d\d\d-\d\d-\d\d$/';

    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $patterns = [
            new RegexPattern(self::INT, [PsalmType::Int], [[PsalmType::String, PsalmType::NumericString]]),
            new RegexPattern(self::NO_UNDERSCORE, [PsalmType::String], []),
        ];
        $parser   = new RegexParser(...$patterns);
        $actual   = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'  => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'no regex' => [new Regex(self::DATE), [PsalmType::Bool], [PsalmType::Bool]],
            'replace'  => [new Regex(self::INT), [PsalmType::Int, PsalmType::String], [PsalmType::Int, PsalmType::NumericString]],
            'filter'   => [new Regex(self::NO_UNDERSCORE), [PsalmType::String, PsalmType::Null], [PsalmType::String]],
        ];
        // phpcs:enable
    }
}
