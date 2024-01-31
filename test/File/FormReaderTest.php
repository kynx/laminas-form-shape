<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\File;

use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReader;
use KynxTest\Laminas\FormShape\File\Asset\TestForm;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\PluginManagerInterface;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function file_get_contents;

#[CoversClass(FormReader::class)]
final class FormReaderTest extends TestCase
{
    #[DataProvider('getFormFileProvider')]
    public function testGetFormFile(string $name, ?FormFile $expected): void
    {
        $formReader = new FormReader(self::getFormElementManager());

        $actual = $formReader->getFormFile(__DIR__ . "/Asset/$name.php");
        self::assertEquals($expected, $actual);
    }

    public static function getFormFileProvider(): array
    {
        $path     = __DIR__ . '/Asset/TestForm.php';
        $form     = self::getFormElementManager()->get(TestForm::class);
        $formFile = new FormFile($path, PhpFile::fromCode(file_get_contents($path)), $form);

        return [
            'nonexistent'  => ['Nonexistent', null],
            'no namespace' => ['NoNamespace', null],
            'no class'     => ['NoClass', null],
            'exists'       => ['TestForm', $formFile],
        ];
    }

    public function testGetFormFileHandlesFormElementManagerException(): void
    {
        $formElementManager = self::createMock(PluginManagerInterface::class);
        $formElementManager->expects(self::once())
            ->method('get')
            ->with(TestForm::class)
            ->willThrowException(self::createStub(ContainerExceptionInterface::class));
        $formReader = new FormReader($formElementManager);

        $actual = $formReader->getFormFile(__DIR__ . '/Asset/TestForm.php');
        self::assertNull($actual);
    }

    private static function getFormElementManager(): FormElementManager
    {
        return new FormElementManager(self::createStub(ContainerInterface::class));
    }
}
