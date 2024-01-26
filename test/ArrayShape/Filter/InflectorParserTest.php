<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\InflectorParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\Inflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InflectorParser::class)]
final class InflectorParserTest extends TestCase
{
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(FilterInterface $filter, array $expected): void
    {
        $parser = new InflectorParser();
        $actual = $parser->getTypes($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid'   => [new Boolean(), [PsalmType::String, PsalmType::Null]],
            'inflector' => [new Inflector(), [PsalmType::String]],
        ];
    }
}
