<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StepParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\Step;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\StepParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class StepParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new StepParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Step(), [PsalmType::String], []],
            'numbers' => [new Step(), [PsalmType::Bool, PsalmType::Int, PsalmType::Float], [PsalmType::Int, PsalmType::Float]],
        ];
        // phpcs:enable
    }
}
