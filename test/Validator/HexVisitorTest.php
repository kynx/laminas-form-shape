<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\HexVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Hex;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;

#[CoversClass(HexVisitor::class)]
final class HexVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'string' => [
                new Hex(),
                [new TString(), new TNull()],
                [new TNonEmptyString()],
            ],
            'int'    => [
                new Hex(),
                [new TInt()],
                [new TInt()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new HexVisitor();
    }
}
