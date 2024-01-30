<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator\Sitemap;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\Sitemap\PriorityVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(PriorityVisitor::class)]
final class PriorityVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new PriorityVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        return [
            'invalid' => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'string'  => [new Priority(), [PsalmType::String, PsalmType::Null], [PsalmType::NumericString]],
            'int'     => [new Priority(), [PsalmType::Int, PsalmType::Null], [PsalmType::Int]],
            'float'   => [new Priority(), [PsalmType::Float, PsalmType::Null], [PsalmType::Float]],
        ];
    }
}
