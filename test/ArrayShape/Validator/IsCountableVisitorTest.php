<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Countable;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsCountableVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\IsCountable;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(IsCountableVisitor::class)]
final class IsCountableVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new IsCountableVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function visitProvider(): array
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
