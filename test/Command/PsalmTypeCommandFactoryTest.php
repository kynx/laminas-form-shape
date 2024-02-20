<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommandFactory;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommandFactory::class)]
final class PsalmTypeCommandFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $formLocator = self::createStub(FormLocatorInterface::class);
        $formVisitor = self::createStub(FormVisitorInterface::class);
        $container   = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [FormLocatorInterface::class, $formLocator],
                [FormVisitorInterface::class, $formVisitor],
                [DecoratorInterface::class, new PrettyPrinter()],
                [TypeNamerInterface::class, new TypeNamer('T{shortName}Type')],
            ]);

        $factory  = new PsalmTypeCommandFactory();
        $instance = $factory($container);

        $shape    = new Union([new TInt()]);
        $formFile = new FormFile(new ReflectionClass(Form::class), new Form());
        $formLocator->method('locate')
            ->willReturn([$formFile]);
        $formVisitor->method('visit')
            ->willReturn($shape);

        $commandTester = new CommandTester($instance);
        $actual        = $commandTester->execute([
            '--output' => true,
            'path'     => [$formFile->reflection->getFileName()],
        ]);
        self::assertSame(Command::SUCCESS, $actual);
        self::assertStringContainsString('TFormType = int', $commandTester->getDisplay());
    }
}
