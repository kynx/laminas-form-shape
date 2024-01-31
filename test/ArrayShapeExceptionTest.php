<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape;

use Kynx\Laminas\FormShape\InputVisitorException;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputVisitorException::class)]
final class ArrayShapeExceptionTest extends TestCase
{
    public function testNoVisitorForInput(): void
    {
        $expected  = "No input visitor configured for '" . Input::class . "'";
        $input     = new Input();
        $exception = InputVisitorException::noVisitorForInput($input);
        self::assertSame($expected, $exception->getMessage());
    }

    public function testCannotParseInputTypeSetsMessage(): void
    {
        $expected  = "Cannot get type for 'foo'";
        $input     = new Input('foo');
        $exception = InputVisitorException::cannotGetInputType($input);
        self::assertSame($expected, $exception->getMessage());
    }
}
