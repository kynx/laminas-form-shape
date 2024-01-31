<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\FormShapeCommandFactory;
use Kynx\Laminas\FormShape\Decorator\ArrayShapeDecorator;
use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReader;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\Form\Form;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(FormShapeCommandFactory::class)]
final class FormShapeCommandFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $formReader         = self::createStub(FormReaderInterface::class);
        $inputFilterVisitor = self::createStub(InputFilterVisitorInterface::class);
        $container          = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [FormReader::class, $formReader],
                [InputFilterVisitorInterface::class, $inputFilterVisitor],
                [ArrayShapeDecorator::class, new ArrayShapeDecorator()],
            ]);

        $factory  = new FormShapeCommandFactory();
        $instance = $factory($container);

        $shape    = new ArrayShape('', [new ElementShape('foo', [PsalmType::Int])]);
        $formFile = new FormFile(__DIR__ . '/Form.php', new PhpFile(), new Form());
        $formReader->method('getFormFile')
            ->willReturn($formFile);
        $inputFilterVisitor->method('visit')
            ->willReturn($shape);

        $commandTester = new CommandTester($instance);
        $actual        = $commandTester->execute(['path' => $formFile->fileName]);
        self::assertSame(Command::SUCCESS, $actual);
    }
}
