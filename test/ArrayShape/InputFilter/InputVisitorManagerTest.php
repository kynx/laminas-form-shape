<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
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
