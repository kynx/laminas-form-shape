<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ArrayShapeException;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputVisitorManager::class)]
final class InputVisitorManagerTest extends TestCase
{
    public function testGetVisitorReturnsInputVisitor(): void
    {
        $expected = self::createStub(InputVisitorInterface::class);
        $manager  = new InputVisitorManager([Input::class => $expected]);
        $actual   = $manager->getVisitor(new Input());
        self::assertSame($expected, $actual);
    }

    public function testGetVisitorThrowsExceptionForMissingVisitor(): void
    {
        $expected = "No input visitor configured for '" . Input::class . "'";
        $manager  = new InputVisitorManager([]);
        self::expectException(ArrayShapeException::class);
        self::expectExceptionMessage($expected);
        $manager->getVisitor(new Input());
    }
}
