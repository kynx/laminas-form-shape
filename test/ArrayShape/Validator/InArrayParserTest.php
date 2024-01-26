<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\InArray;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
#[CoversClass(InArrayParser::class)]
final class InArrayParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesLiteralProvider')]
    public function testGetTypesReturnsLiteral(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new InArrayParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypesLiteralProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'         => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'empty'           => [new InArray(['haystack' => []]), [PsalmType::Bool], [PsalmType::Bool]],
            'strict null'     => [new InArray(['haystack' => [null], 'strict' => true]), [PsalmType::String, PsalmType::Null], [PsalmType::Null]],
            'lax null'        => [new InArray(['haystack' => [null], 'strict' => false]), [PsalmType::String, PsalmType::Null], [PsalmType::Null, PsalmType::String]],
            'strict string'   => [new InArray(['haystack' => ['foo'], 'strict' => true]), [PsalmType::String], [new Literal(["foo"])]],
            'lax string'      => [new InArray(['haystack' => ['foo'], 'strict' => false]), [PsalmType::String], [new Literal(["foo"])]],
            'strict int'      => [new InArray(['haystack' => [123], 'strict' => true]), [PsalmType::String, PsalmType::Int], [new Literal([123])]],
            'lax int'         => [new InArray(['haystack' => [123], 'strict' => false]), [PsalmType::String, PsalmType::Int], [new Literal([123, "123"])]],
            'strict float'    => [new InArray(['haystack' => [1.23], 'strict' => true]), [PsalmType::Float], [PsalmType::Float]],
            'lax float'       => [new InArray(['haystack' => [1.23], 'strict' => false]), [PsalmType::Float], [PsalmType::Float]],
            'strict multiple' => [new InArray(['haystack' => ['foo', null], 'strict' => true]), [PsalmType::String, PsalmType::Null], [PsalmType::Null, new Literal(["foo"])]],
            'lax multiple'    => [new InArray(['haystack' => ['foo', null], 'strict' => false]), [PsalmType::String, PsalmType::Null], [PsalmType::Null, PsalmType::String, new Literal(["foo"])]],
        ];
        // phpcs:enable
    }

    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesTypeProvider')]
    public function testGetTypesReturnsType(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new InArrayParser(false, 0);
        $actual = $parser->getTypes($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypesTypeProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'empty'         => [new InArray(['haystack' => []]), [PsalmType::String], []],
            'strict null'   => [new InArray(['haystack' => [null], 'strict' => true]), [PsalmType::String, PsalmType::Null], [PsalmType::Null]],
            'lax null'      => [new InArray(['haystack' => [null], 'strict' => false]), [PsalmType::String, PsalmType::Null], [PsalmType::Null, PsalmType::String]],
            'strict string' => [new InArray(['haystack' => ['foo'], 'strict' => true]), [PsalmType::String], [PsalmType::String]],
            'lax string'    => [new InArray(['haystack' => ['foo'], 'strict' => false]), [PsalmType::String], [PsalmType::String]],
            'strict int'    => [new InArray(['haystack' => [123], 'strict' => true]), [PsalmType::String, PsalmType::Int], [PsalmType::Int]],
            'lax int'       => [new InArray(['haystack' => [123], 'strict' => false]), [PsalmType::String, PsalmType::Int], [PsalmType::Int, PsalmType::String]],
            'strict float'  => [new InArray(['haystack' => [1.23], 'strict' => true]), [PsalmType::Float], [PsalmType::Float]],
            'lax float'     => [new InArray(['haystack' => [1.23], 'strict' => false]), [PsalmType::String, PsalmType::Float], [PsalmType::Float, PsalmType::String]],
        ];
        // phpcs:enable
    }
}
