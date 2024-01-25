<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanParser
 */
final class BooleanParserTest extends TestCase
{
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(FilterInterface $filter, array $expected): void
    {
        $parser = new BooleanParser();
        $actual = $parser->getTypes($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function getTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid filter' => [new AllowList(), [PsalmType::String, PsalmType::Null]],
            'casting'        => [new Boolean(['casting' => true]), [PsalmType::Bool]],
            'null'           => [new Boolean(['casting' => false, 'type' => Boolean::TYPE_NULL]), [PsalmType::String, PsalmType::Bool]],
        ];
        // phpcs:enable
    }
}
