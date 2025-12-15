<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommandFactory;
use Kynx\Laminas\FormShape\Form\FormProcessor;
use Kynx\Laminas\FormShape\Form\FormProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PsalmTypeCommandFactory::class)]
final class PsalmTypeCommandFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $formProcessor = self::createMock(FormProcessorInterface::class);
        $container     = $this->getContainer($formProcessor);
        $factory       = new PsalmTypeCommandFactory();
        $instance      = $factory($container);

        $formProcessor->expects(self::once())
            ->method('process');

        $commandTester = new CommandTester($instance);
        $actual        = $commandTester->execute([
            'path' => 'src',
        ]);
        self::assertSame(Command::SUCCESS, $actual);
    }

    public function testInvokeAddsPhpCodeSnifferFixer(): void
    {
        $formProcessor = self::createStub(FormProcessorInterface::class);
        $container     = $this->getContainer($formProcessor);
        $factory       = new PsalmTypeCommandFactory();
        $instance      = $factory($container);

        $actual = $instance->getDefinition()->hasOption('cs-fix');
        self::assertTrue($actual);
    }

    private function getContainer(FormProcessorInterface $formProcessor): ContainerInterface
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [FormProcessor::class, $formProcessor],
            ]);

        return $container;
    }
}
