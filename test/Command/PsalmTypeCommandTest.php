<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommand;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Laminas\Form\Form;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommand::class)]
final class PsalmTypeCommandTest extends TestCase
{
    private FormReaderInterface&Stub $formReader;
    private FormVisitorInterface&Stub $formVisitor;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formReader  = self::createStub(FormReaderInterface::class);
        $this->formVisitor = self::createStub(FormVisitorInterface::class);
        $decorator         = new PrettyPrinter();

        $command             = new PsalmTypeCommand(
            $this->formReader,
            $this->formVisitor,
            $decorator
        );
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteReturnsInvalidErrorForInvalidFile(): void
    {
        $this->formReader->method('getFormFile')
            ->willReturn(null);

        $actual = $this->commandTester->execute(['path' => __DIR__ . '/nonexistent.php']);
        self::assertSame(Command::INVALID, $actual);
        self::assertStringContainsString('Cannot find form', $this->commandTester->getDisplay());
    }

    public function testExecuteReturnsFailureErrorForArrayShapeException(): void
    {
        $exception = new InputVisitorException('Test fail');
        $formFile  = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $this->formReader->method('getFormFile')
            ->willReturn($formFile);
        $this->formVisitor->method('visit')
            ->willThrowException($exception);

        $actual = $this->commandTester->execute(['path' => $formFile->fileName]);
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
        $formFile = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $this->formReader->method('getFormFile')
            ->willReturn($formFile);
        $this->formVisitor->method('visit')
            ->willReturn($expected);

        $actual = $this->commandTester->execute(['path' => $formFile->fileName]);
        self::assertSame(Command::SUCCESS, $actual);
        self::assertStringContainsString('foo: int,', $this->commandTester->getDisplay());
    }
}
