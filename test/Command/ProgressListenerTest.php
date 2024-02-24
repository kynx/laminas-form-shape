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
    private StyleInterface&MockObject $style;
    private string $cwd;
    private ProgressListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->style = self::createMock(StyleInterface::class);
        $this->cwd   = substr(__DIR__, 0, (int) strrpos(__DIR__, '/test/Command'));

        $this->listener = new ProgressListener($this->style, null, $this->cwd, ['test']);
    }

    public function testErrorOutputsError(): void
    {
        $expected = 'Foo error';
        $this->style->expects(self::once())
            ->method('error')
            ->with($expected);
        $this->listener->error($expected);
        self::assertSame(Command::FAILURE, $this->listener->getStatus());
    }

    public function testSuccessOutputsPath(): void
    {
        $expected = 'Processed test/Command/ProgressListenerTest.php';
        $this->style->expects(self::once())
            ->method('text')
            ->with($expected);
        $this->listener->success(new ReflectionClass($this));
        self::assertSame(Command::SUCCESS, $this->listener->getStatus());
    }

    public function testSuccessAddsFileToFixer(): void
    {
        $expected = substr(__FILE__, strlen($this->cwd) + 1);
        $fixer    = new MockFixer();
        $listener = new ProgressListener($this->style, $fixer, $this->cwd, ['test']);
        $listener->success(new ReflectionClass($this));
        self::assertSame([$expected], $fixer->paths);
    }

    public function testFinallyNoneProcessedOutputsError(): void
    {
        $expected = "Cannot find any forms at 'test'";
        $this->style->expects(self::once())
            ->method('error')
            ->with($expected);
        $this->listener->finally(0);
        self::assertSame(Command::INVALID, $this->listener->getStatus());
    }

    public function testFinallyOutputsProcessed(): void
    {
        $expected = 'Added types to 123 forms';
        $this->style->expects(self::once())
            ->method('success')
            ->with($expected);
        $this->listener->finally(123);
        self::assertSame(Command::SUCCESS, $this->listener->getStatus());
    }
}
