<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\ProgressListener;
use KynxTest\Laminas\FormShape\CodingStandards\MockFixer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\StyleInterface;

use function strlen;
use function strrpos;
use function substr;

#[CoversClass(ProgressListener::class)]
final class ProgressListenerTest extends TestCase
{
    private string $cwd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cwd = substr(__DIR__, 0, (int) strrpos(__DIR__, '/test/Command'));
    }

    private function getProgressListener(StyleInterface&MockObject $style): ProgressListener
    {
        return new ProgressListener($style, null, $this->cwd, ['test']);
    }

    public function testErrorOutputsError(): void
    {
        $expected = 'Foo error';
        $style    = $this->createMock(StyleInterface::class);
        $style->expects(self::once())
            ->method('error')
            ->with($expected);
        $listener = $this->getProgressListener($style);
        $listener->error($expected);
        self::assertSame(Command::FAILURE, $listener->getStatus());
    }

    public function testSuccessOutputsPath(): void
    {
        $expected = 'Processed test/Command/ProgressListenerTest.php';
        $style    = $this->createMock(StyleInterface::class);
        $style->expects(self::once())
            ->method('text')
            ->with($expected);

        $listener = $this->getProgressListener($style);
        $listener->success(new ReflectionClass($this));
        self::assertSame(Command::SUCCESS, $listener->getStatus());
    }

    public function testSuccessAddsFileToFixer(): void
    {
        $expected = substr(__FILE__, strlen($this->cwd) + 1);
        $style    = self::createStub(StyleInterface::class);
        $fixer    = new MockFixer();
        $listener = new ProgressListener($style, $fixer, $this->cwd, ['test']);
        $listener->success(new ReflectionClass($this));
        self::assertSame([$expected], $fixer->paths);
    }

    public function testFinallyNoneProcessedOutputsError(): void
    {
        $expected = "Cannot find any forms at 'test'";
        $style    = $this->createMock(StyleInterface::class);
        $style->expects(self::once())
            ->method('error')
            ->with($expected);
        $listener = $this->getProgressListener($style);
        $listener->finally(0);
        self::assertSame(Command::INVALID, $listener->getStatus());
    }

    public function testFinallyOutputsProcessed(): void
    {
        $expected = 'Added types to 123 forms';
        $style    = $this->createMock(StyleInterface::class);
        $style->expects(self::once())
            ->method('success')
            ->with($expected);
        $listener = $this->getProgressListener($style);
        $listener->finally(123);
        self::assertSame(Command::SUCCESS, $listener->getStatus());
    }
}
