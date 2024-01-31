<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\ArrayShapeException;
use Kynx\Laminas\FormShape\Command\FormShapeCommand;
use Kynx\Laminas\FormShape\Decorator\ArrayShapeDecorator;
use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\Form\Form;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(FormShapeCommand::class)]
final class FormShapeCommandTest extends TestCase
{
    private FormReaderInterface&Stub $formReader;
    private InputFilterVisitorInterface&Stub $inputFilterVisitor;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formReader         = self::createStub(FormReaderInterface::class);
        $this->inputFilterVisitor = self::createStub(InputFilterVisitorInterface::class);

        $command             = new FormShapeCommand(
            $this->formReader,
            $this->inputFilterVisitor,
            new ArrayShapeDecorator()
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
        $exception = new ArrayShapeException('Test fail');
        $formFile  = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $this->formReader->method('getFormFile')
            ->willReturn($formFile);
        $this->inputFilterVisitor->method('visit')
            ->willThrowException($exception);

        $actual = $this->commandTester->execute(['path' => $formFile->fileName]);
        self::assertSame(Command::FAILURE, $actual);
        self::assertStringContainsString($exception->getMessage(), $this->commandTester->getDisplay());
    }

    public function testExecuteReturnsSuccess(): void
    {
        $shape    = new ArrayShape('', [new ElementShape('foo', [PsalmType::Int])]);
        $formFile = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $this->formReader->method('getFormFile')
            ->willReturn($formFile);
        $this->inputFilterVisitor->method('visit')
            ->willReturn($shape);

        $actual = $this->commandTester->execute(['path' => $formFile->fileName]);
        self::assertSame(Command::SUCCESS, $actual);
        self::assertStringContainsString('foo: int,', $this->commandTester->getDisplay());
    }
}
