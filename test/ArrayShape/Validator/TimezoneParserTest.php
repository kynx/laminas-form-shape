<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\TimezoneParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Timezone;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
#[CoversClass(TimezoneParser::class)]
final class TimezoneParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypeProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new TimezoneParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypeProvider(): array
    {
        return [
            'invalid'     => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'empty'       => [new Timezone(), [], []],
            'no existing' => [new Timezone(), [PsalmType::Int], []],
            'timezone'    => [new Timezone(), [PsalmType::String, PsalmType::Null], [PsalmType::NonEmptyString]],
        ];
    }
}
