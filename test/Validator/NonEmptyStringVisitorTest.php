<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Barcode;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;

#[CoversClass(NonEmptyStringVisitor::class)]
final class NonEmptyStringVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'barcode' => [
                new Barcode(),
                [new TString(), new TNull()],
                [new TNonEmptyString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new NonEmptyStringVisitor();
    }
}
