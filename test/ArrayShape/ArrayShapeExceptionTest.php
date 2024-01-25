<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException
 */
final class ArrayShapeExceptionTest extends TestCase
{
    public function testCannotParseInputTypeSetsMessage(): void
    {
        $expected  = "Cannot parse type for 'foo'";
        $input     = new Input('foo');
        $exception = ArrayShapeException::cannotParseInputType($input);
        self::assertSame($expected, $exception->getMessage());
    }
}
