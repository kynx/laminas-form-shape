<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\RegexVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(RegexVisitor::class)]
final class RegexVisitorTest extends AbstractValidatorVisitorTestCase
{
    private const INT           = '/^\d+$/';
    private const NO_UNDERSCORE = '/^[^_]*$/';
    private const DATE          = '/^\d\d\d\d-\d\d-\d\d$/';

    public static function visitProvider(): array
    {
        return [
            'no regex' => [
                new Regex(self::DATE),
                [new TScalar()],
                [new TFloat(), new TInt(), new TString()],
            ],
            'replace'  => [
                new Regex(self::INT),
                [new TInt(), new TString()],
                [new TInt(), new TNumericString()],
            ],
            'narrows'  => [
                new Regex(self::NO_UNDERSCORE),
                [new TString(), new TNull()],
                [new TNonEmptyString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        $patterns = [
            self::INT           => new Union([new TInt(), new TNumericString()]),
            self::NO_UNDERSCORE => new Union([new TNonEmptyString()]),
        ];

        return new RegexVisitor($patterns);
    }
}
