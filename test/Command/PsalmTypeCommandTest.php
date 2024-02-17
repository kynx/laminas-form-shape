<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommand;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommand::class)]
final class PsalmTypeCommandTest extends TestCase
{
    private FormLocatorInterface&Stub $formLocator;
    private FormVisitorInterface&Stub $formVisitor;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formLocator = self::createStub(FormLocatorInterface::class);
        $this->formVisitor = self::createStub(FormVisitorInterface::class);
        $decorator         = new PrettyPrinter();

        $command             = new PsalmTypeCommand(
            $this->formLocator,
            $this->formVisitor,
            $decorator
        );
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteReturnsInvalidErrorForInvalidFile(): void
    {
        $this->formLocator->method('locate')
            ->willReturn([]);

        $actual = $this->commandTester->execute(['path' => [__DIR__ . '/nonexistent.php']]);
        self::assertSame(Command::INVALID, $actual);
        self::assertStringContainsString('Cannot find any forms', $this->commandTester->getDisplay());
    }

    public function testExecuteReturnsFailureErrorForArrayShapeException(): void
    {
        $exception = new InputVisitorException('Test fail');
        $formFile  = new FormFile(new ReflectionClass(Form::class), new Form());
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willThrowException($exception);

        $actual = $this->commandTester->execute(['path' => [$formFile->reflection->getFileName()]]);
        self::assertSame(Command::FAILURE, $actual);
        self::assertStringContainsString($exception->getMessage(), $this->commandTester->getDisplay());
    }

    public function testExecuteReturnsSuccess(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);
        $formFile = new FormFile(new ReflectionClass(Form::class), new Form());
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willReturn($expected);

        $actual = $this->commandTester->execute(['path' => [$formFile->reflection->getFileName()]]);
        self::assertSame(Command::SUCCESS, $actual);
        self::assertStringContainsString('foo: int,', $this->commandTester->getDisplay());
    }
}
