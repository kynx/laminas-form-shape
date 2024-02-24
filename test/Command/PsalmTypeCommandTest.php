<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\ProgressListener;
use Kynx\Laminas\FormShape\Command\PsalmTypeCommand;
use Kynx\Laminas\FormShape\Form\FormProcessorInterface;
use KynxTest\Laminas\FormShape\CodingStandards\MockFixer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommand::class)]
final class PsalmTypeCommandTest extends TestCase
{
    private FormProcessorInterface&MockObject $formProcessor;
    private MockFixer $fixer;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formProcessor = self::createMock(FormProcessorInterface::class);
        $this->fixer         = new MockFixer();

        $command             = new PsalmTypeCommand($this->formProcessor, $this->fixer);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteProcessesPaths(): void
    {
        $this->formProcessor->expects(self::once())
            ->method('process')
            ->with(['src'], self::isInstanceOf(ProgressListener::class), true, false);

        $actual = $this->commandTester->execute([
            'path' => 'src',
        ]);
        self::assertSame(Command::SUCCESS, $actual);
    }

    public function testExecuteDoesNotProcessFieldsets(): void
    {
        $this->formProcessor->expects(self::once())
            ->method('process')
            ->with(['src'], self::isInstanceOf(ProgressListener::class), false, false);

        $actual = $this->commandTester->execute([
            '--no-fieldset-types' => true,
            'path'                => 'src',
        ]);

        self::assertSame(Command::SUCCESS, $actual);
    }

    public function testExecuteRemovesGetDataReturn(): void
    {
        $this->formProcessor->expects(self::once())
            ->method('process')
            ->with(['src'], self::isInstanceOf(ProgressListener::class), true, true);

        $actual = $this->commandTester->execute([
            '--remove-getdata-return' => true,
            'path'                    => 'src',
        ]);

        self::assertSame(Command::SUCCESS, $actual);
    }

    public function testExecuteRunsCsFixer(): void
    {
        $this->commandTester->execute([
            '--cs-fix' => true,
            'path'     => 'src',
        ]);

        self::assertTrue($this->fixer->fixed);
    }

    public function testExecuteReturnsListenerStatus(): void
    {
        $this->formProcessor->method('process')
            ->willReturnCallback(static function (array $paths, ProgressListener $listener): void {
                $listener->finally(0);
            });

        $actual = $this->commandTester->execute([
            'path' => 'src',
        ]);
        self::assertSame(Command::INVALID, $actual);
    }
}
