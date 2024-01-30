<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\ClassString;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\IsInstanceOfVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(IsInstanceOfVisitor::class)]
final class IsInstanceOfVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $visitor = new IsInstanceOfVisitor();
        $actual  = $visitor->visit($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'    => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'instanceof' => [new IsInstanceOf(['className' => stdClass::class]), [PsalmType::String], [new ClassString(stdClass::class)]],
        ];
        // phpcs:enable
    }
}
