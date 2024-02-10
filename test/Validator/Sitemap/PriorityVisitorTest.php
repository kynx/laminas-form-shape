<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator\Sitemap;

use Kynx\Laminas\FormShape\Validator\Sitemap\PriorityVisitor;
use KynxTest\Laminas\FormShape\Validator\AbstractValidatorVisitorTestCase;
use Laminas\Validator\Sitemap\Priority;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(PriorityVisitor::class)]
final class PriorityVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'string' => [
                new Priority(),
                [new TString(), new TNull()],
                [new TNumericString()],
            ],
            'int'    => [
                new Priority(),
                [new TInt(), new TNull()],
                [new TIntRange(0, 1)],
            ],
            'float'  => [
                new Priority(),
                [new TFloat(), new TNull()],
                [new TFloat()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): PriorityVisitor
    {
        return new PriorityVisitor();
    }
}
