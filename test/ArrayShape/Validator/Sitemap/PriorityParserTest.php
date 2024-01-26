<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator\Sitemap;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\Sitemap\PriorityParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\Sitemap\PriorityParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class PriorityParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new PriorityParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Priority(), [PsalmType::String, PsalmType::Null], [PsalmType::NumericString]],
            'int'     => [new Priority(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'float'   => [new Priority(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
        ];
    }
}
