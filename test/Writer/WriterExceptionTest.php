<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Exception;
use Kynx\Laminas\FormShape\Writer\WriterException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(WriterException::class)]
final class WriterExceptionTest extends TestCase
{
    public function testFromReadReturnsException(): void
    {
        $expected  = "Could not parse " . __FILE__ . ": Foo";
        $exception = WriterException::fileRead(new ReflectionClass($this), new Exception('Foo'));
        self::assertSame($expected, $exception->getMessage());
    }

    public function testClassNotFoundReturnsException(): void
    {
        $expected  = "Could not find class " . self::class . " in file " . __FILE__;
        $exception = WriterException::classNotFound(new ReflectionClass($this));
        self::assertSame($expected, $exception->getMessage());
    }
}
