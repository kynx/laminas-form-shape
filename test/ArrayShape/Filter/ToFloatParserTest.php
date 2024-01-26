<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\ToFloatParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToFloat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToFloatParser::class)]
final class ToFloatParserTest extends TestCase
{
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(FilterInterface $filter, array $expected): void
    {
        $parser = new ToFloatParser();
        $actual = $parser->getTypes($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid' => [new Boolean(), [PsalmType::String, PsalmType::Null]],
            'float'   => [new ToFloat(), [PsalmType::String, PsalmType::Null, PsalmType::Float]],
        ];
    }
}
