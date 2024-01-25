<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntParser
 */
final class ToIntParserTest extends TestCase
{
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(FilterInterface $filter, array $expected): void
    {
        $parser = new ToIntParser();
        $actual = $parser->getTypes($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        return [
            'invalid filter' => [new Boolean(), [PsalmType::String, PsalmType::Null]],
            'int'            => [new ToInt(), [PsalmType::String, PsalmType::Null, PsalmType::Int]],
        ];
    }
}
