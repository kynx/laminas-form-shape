<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\RecursiveFormFileIterator;
use KynxTest\Laminas\FormShape\Locator\Asset\AbstractForm;
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
    private RecursiveArrayIterator $innerIterator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->innerIterator = new RecursiveArrayIterator([], RecursiveArrayIterator::CHILD_ARRAYS_ONLY);
    }

    private function getIterator(PluginManagerInterface&MockObject $formElementManager): RecursiveFormFileIterator
    {
        return new RecursiveFormFileIterator($this->innerIterator, $formElementManager);
    }

    public function testCurrentReturnsNullForNonReflectionEntry(): void
    {
        $this->innerIterator->offsetSet(0, null);
        $this->innerIterator->rewind();

        $formElementManager = self::createStub(PluginManagerInterface::class);
        $iterator           = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);
        $actual             = $iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsNullForAbstractForm(): void
    {
        $reflection = new ReflectionClass(AbstractForm::class);
        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();

        $formElementManager = self::createStub(PluginManagerInterface::class);
        $iterator           = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);
        $actual             = $iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsNullWhenFormElementManagerThrowsException(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();
        $formElementManager = $this->createMock(PluginManagerInterface::class);
        $formElementManager->expects(self::once())
            ->method('get')
            ->willThrowException(new InvalidElementException());

        $actual = $this->getIterator($formElementManager)->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsNullWhenFormElementManagerReturnsFieldset(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();

        $formElementManager = self::createStub(PluginManagerInterface::class);
        $formElementManager->method('get')
            ->willReturn(new Fieldset());
        $iterator = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);

        $actual = $iterator->current();
        self::assertNull($actual);
    }

    public function testCurrentReturnsFormFile(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $form       = new Form();
        $expected   = new FormFile($reflection, $form);

        $this->innerIterator->offsetSet(0, $reflection);
        $this->innerIterator->rewind();

        $formElementManager = self::createStub(PluginManagerInterface::class);
        $formElementManager->method('get')
            ->willReturn($form);
        $iterator = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);

        $actual = $iterator->current();
        self::assertEquals($expected, $actual);
    }

    public function testGetChildrenReturnsNull(): void
    {
        $formElementManager = self::createStub(PluginManagerInterface::class);
        $iterator           = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);
        $actual             = $iterator->getChildren();
        /** @psalm-suppress TypeDoesNotContainNull Well, it most certainly does */
        self::assertNull($actual);
    }

    public function testGetChildrenReturnsIterator(): void
    {
        $reflection = new ReflectionClass(TestForm::class);
        $this->innerIterator->offsetSet(0, [0 => $reflection]);
        $this->innerIterator->rewind();

        $formElementManager = self::createStub(PluginManagerInterface::class);
        $formElementManager->method('get')
            ->willReturn(new Form());
        $iterator = new RecursiveFormFileIterator($this->innerIterator, $formElementManager);

        $children = $iterator->getChildren();
        self::assertInstanceOf(RecursiveFormFileIterator::class, $children);
        self::assertInstanceOf(FormFile::class, $children->current());
    }
}
