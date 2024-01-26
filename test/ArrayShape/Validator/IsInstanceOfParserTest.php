<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsInstanceOfParser;
use Laminas\Validator\Barcode;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
#[CoversClass(IsInstanceOfParser::class)]
final class IsInstanceOfParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $parser = new IsInstanceOfParser();
        $actual = $parser->getTypes($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'    => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'instanceof' => [new IsInstanceOf(['className' => stdClass::class]), [PsalmType::String], [new ClassString(stdClass::class)]],
        ];
        // phpcs:enable
    }
}
