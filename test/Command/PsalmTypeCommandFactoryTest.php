<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommandFactory;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReader;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Laminas\Form\Form;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommandFactory::class)]
final class PsalmTypeCommandFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $formReader  = self::createStub(FormReaderInterface::class);
        $formVisitor = self::createStub(FormVisitorInterface::class);
        $container   = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [FormReader::class, $formReader],
                [FormVisitorInterface::class, $formVisitor],
                [DecoratorInterface::class, new PrettyPrinter()],
            ]);

        $factory  = new PsalmTypeCommandFactory();
        $instance = $factory($container);

        $shape    = new Union([new TInt()]);
        $formFile = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $formReader->method('getFormFile')
            ->willReturn($formFile);
        $formVisitor->method('visit')
            ->willReturn($shape);

        $commandTester = new CommandTester($instance);
        $actual        = $commandTester->execute(['path' => $formFile->fileName]);
        self::assertSame(Command::SUCCESS, $actual);
    }
}
