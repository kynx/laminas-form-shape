<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\RecursiveFormFileIterator;
use KynxTest\Laminas\FormShape\Locator\Asset\TestForm;
use Laminas\Form\Exception\InvalidElementException;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\ServiceManager\PluginManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RecursiveArrayIterator;
use ReflectionClass;

#[CoversClass(RecursiveFormFileIterator::class)]
final class RecursiveFormFileIteratorTest extends TestCase
{
    private PluginManagerInterface&MockObject $formElementManager;
    private RecursiveArrayIterator $innerIterator;
    private RecursiveFormFileIterator $iterator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formElementManager = $this->createMock(PluginManagerInterface::class);
        $this->innerIterator      = new RecursiveArrayIterator([], RecursiveArrayIterator::CHILD_ARRAYS_ONLY);
        $this->iterator           = new RecursiveFormFileIterator($this->innerIterator, $this->formElementManager);
    }

    public function testCurrentReturnsNullForNonReflectionEntry(): void
    {
        $this->innerIterator->offsetSet(0, null);
        $this->innerIterator->rewind();

        $actual = $this->iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsNullWhenFormElementManagerThrowsException(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();
        $this->formElementManager->expects(self::once())
            ->method('get')
            ->willThrowException(new InvalidElementException());

        $actual = $this->iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsNullWhenFormElementManagerReturnsFieldset(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();
        $this->formElementManager->method('get')
            ->willReturn(new Fieldset());

        $actual = $this->iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsFormFile(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $form       = new Form();
        $expected   = new FormFile($reflection, $form);

        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();
        $this->formElementManager->method('get')
            ->willReturn($form);

        $actual = $this->iterator->current();
        self::assertEquals($expected, $actual);
    }

    public function testGetChildrenReturnsNull(): void
    {
        $actual = $this->iterator->getChildren();
        /** @psalm-suppress TypeDoesNotContainNull Well, it most certainly does */
        self::assertNull($actual);
    }

    public function testGetChildrenReturnsIterator(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, [0 => $reflection]);
        $this->innerIterator->rewind();

        $this->formElementManager->method('get')
            ->willReturn(new Form());

        $children = $this->iterator->getChildren();
        self::assertInstanceOf(RecursiveFormFileIterator::class, $children);
        self::assertInstanceOf(FormFile::class, $children->current());
    }
}
