<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\StringLengthVisitor;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;

#[CoversClass(StringLengthVisitor::class)]
final class StringLengthVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'zero min'   => [
                new StringLength(['min' => 0]),
                [new TString(), new TNull()],
                [new TString()],
            ],
            'min length' => [
                new StringLength(['min' => 1]),
                [new TString()],
                [new TNonEmptyString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): StringLengthVisitor
    {
        return new StringLengthVisitor();
    }
}
