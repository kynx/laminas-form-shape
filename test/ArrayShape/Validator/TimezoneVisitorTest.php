<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\Validator\TimezoneVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Timezone;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(TimezoneVisitor::class)]
final class TimezoneVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypeProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new TimezoneVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypeProvider(): array
    {
        return [
            'invalid'     => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'empty'       => [new Timezone(), [], []],
            'no existing' => [new Timezone(), [PsalmType::Int], []],
            'timezone'    => [new Timezone(), [PsalmType::String, PsalmType::Null], [PsalmType::NonEmptyString]],
        ];
    }
}
