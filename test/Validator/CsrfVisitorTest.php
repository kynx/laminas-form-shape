<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\CsrfVisitor;
use Laminas\Validator\Csrf;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;

#[CoversClass(CsrfVisitor::class)]
final class CsrfVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'string' => [
                new Csrf(),
                [new TString(), new TNull()],
                [new TNonEmptyString()],
            ],
            'int'    => [
                new Csrf(),
                [new TInt()],
                [],
            ],
        ];
    }

    protected static function getValidatorVisitor(): CsrfVisitor
    {
        return new CsrfVisitor();
    }
}
