<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Countable;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsCountableParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\IsCountable;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\IsCountableParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class IsCountableParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new IsCountableParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'      => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'array'        => [new IsCountable(), [PsalmType::Array], [PsalmType::Array, new ClassString(Countable::class)]],
            'no countable' => [new IsCountable(), [PsalmType::String], []],
        ];
        // phpcs:enable
    }
}
