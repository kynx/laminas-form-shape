<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator\Sitemap;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\Sitemap\PriorityVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(PriorityVisitor::class)]
final class PriorityVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new PriorityVisitor();
        $actual  = $visitor->getTypes($validator, $existing);
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
