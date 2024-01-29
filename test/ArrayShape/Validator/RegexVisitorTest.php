<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexPattern;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(RegexVisitor::class)]
final class RegexVisitorTest extends TestCase
{
    private const INT           = '/^\d+$/';
    private const NO_UNDERSCORE = '/^[^_]*$/';
    private const DATE          = '/^\d\d\d\d-\d\d-\d\d$/';

    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(ValidatorInterface $validator, array $existing, array $expected): void
    {
        $patterns = [
            new RegexPattern(self::INT, [PsalmType::Int], [[PsalmType::String, PsalmType::NumericString]]),
            new RegexPattern(self::NO_UNDERSCORE, [PsalmType::String, PsalmType::NonEmptyString], []),
        ];
        $visitor  = new RegexVisitor(...$patterns);
        $actual   = $visitor->visit($validator, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'            => [new Barcode(), [PsalmType::Bool], [PsalmType::Bool]],
            'no regex'           => [new Regex(self::DATE), [PsalmType::Bool], [PsalmType::Bool]],
            'replace'            => [new Regex(self::INT), [PsalmType::Int, PsalmType::String], [PsalmType::Int, PsalmType::NumericString]],
            'filter'             => [new Regex(self::NO_UNDERSCORE), [PsalmType::String, PsalmType::Null], [PsalmType::String]],
            'existing invalid'   => [new Regex(self::NO_UNDERSCORE), [PsalmType::Float], []],
            'existing non-empty' => [new Regex(self::NO_UNDERSCORE), [PsalmType::NonEmptyString], [PsalmType::NonEmptyString]],
        ];
        // phpcs:enable
    }
}
