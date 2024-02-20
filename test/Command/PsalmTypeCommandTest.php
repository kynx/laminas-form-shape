<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommand;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(PsalmTypeCommand::class)]
final class PsalmTypeCommandTest extends TestCase
{
    private FormLocatorInterface&Stub $formLocator;
    private FormVisitorInterface&Stub $formVisitor;
    private CommandTester $commandTester;
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile    = tempnam(sys_get_temp_dir(), 'phpunit_');
        $this->formLocator = self::createStub(FormLocatorInterface::class);
        $this->formVisitor = self::createStub(FormVisitorInterface::class);
        $decorator         = new PrettyPrinter();
        $namer             = new TypeNamer('T{shortName}Type');

        $command = new PsalmTypeCommand(
            $this->formLocator,
            $this->formVisitor,
            $decorator,
            $namer
        );

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->tempFile);
    }

    public function testExecuteReturnsInvalidErrorForInvalidFile(): void
    {
        $this->formLocator->method('locate')
            ->willReturn([]);

        $actual = $this->commandTester->execute(['path' => [__DIR__ . '/nonexistent.php']]);
        self::assertSame(Command::INVALID, $actual);
        self::assertStringContainsString('Cannot find any forms', $this->commandTester->getDisplay());
    }

    public function testExecuteReturnsFailureErrorForArrayShapeException(): void
    {
        $exception = new InputVisitorException('Test fail');
        $formFile  = new FormFile(new ReflectionClass(Form::class), new Form());
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willThrowException($exception);

        $actual = $this->commandTester->execute(['path' => [$formFile->reflection->getFileName()]]);
        self::assertSame(Command::FAILURE, $actual);
        self::assertStringContainsString($exception->getMessage(), $this->commandTester->getDisplay());
    }

    public function testExecuteUpdatesForm(): void
    {
        $namespace = $this->getNamespace();
        $expected  = <<<EXPECTED
        <?php
        
        namespace $namespace;
        
        use Laminas\Form\Form;
        use Laminas\Form\FormInterface;
        
        /**
         * @method array{foo: int} getData(int \$flag = FormInterface::VALUES_NORMALIZED)
         * @psalm-type TUpdateFormType = array{
         *     foo: int,
         * }
         * @psalm-template-extends Form<TUpdateFormType>
         */
        class UpdateForm extends Form
        {
            /**
             * @return array{foo: int}
             */
            public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
            {
            }
        }
        EXPECTED;

        $union    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);
        $formFile = $this->getFormFile('UpdateForm');
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willReturn($union);

        $result = $this->commandTester->execute([
            'path' => [$formFile->reflection->getFileName()],
        ]);
        self::assertSame(Command::SUCCESS, $result);
        self::assertStringContainsString('Updated', $this->commandTester->getDisplay());

        $actual = file_get_contents($this->tempFile);
        self::assertSame($expected, $actual);
    }

    public function testExecuteRemovesGetDataReturn(): void
    {
        $union    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);
        $formFile = $this->getFormFile('RemoveGetDataReturn');
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willReturn($union);

        $result = $this->commandTester->execute([
            '--remove-getdata-return' => true,
            'path'                    => [$formFile->reflection->getFileName()],
        ]);
        self::assertSame(Command::SUCCESS, $result);

        $actual = file_get_contents($this->tempFile);
        self::assertStringNotContainsString('@method', $actual);
        self::assertStringNotContainsString('@return', $actual);
    }

    public function testExecuteOutputsType(): void
    {
        $expected = 'foo: int,';
        $union    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);
        $formFile = $this->getFormFile('OutputTypeForm');
        $this->formLocator->method('locate')
            ->willReturn([$formFile]);
        $this->formVisitor->method('visit')
            ->willReturn($union);

        $actual = $this->commandTester->execute([
            '--output' => true,
            'path'     => [$formFile->reflection->getFileName()],
        ]);
        self::assertSame(Command::SUCCESS, $actual);
        self::assertStringContainsString($expected, $this->commandTester->getDisplay());
    }

    private function getFormFile(string $className): FormFile
    {
        $namespace = $this->getNamespace();
        /** @var class-string<FormInterface> $fqcn */
        $fqcn = "$namespace\\$className";
        file_put_contents($this->tempFile, <<<FORM
        <?php
        
        namespace $namespace;
        
        use Laminas\Form\Form;
        use Laminas\Form\FormInterface;
        
        /**
         * @method array{foo: int} getData(int \$flag = FormInterface::VALUES_NORMALIZED)
         */
        class $className extends Form
        {
            /**
             * @return array{foo: int}
             */
            public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
            {
            }
        }
        FORM);

        /** @psalm-suppress UnresolvableInclude */
        require $this->tempFile;

        /** @psalm-suppress ArgumentTypeCoercion */
        $reflection = new ReflectionClass($fqcn);

        $form = new $fqcn();

        return new FormFile($reflection, $form);
    }

    private function getNamespace(): string
    {
        return __NAMESPACE__ . '\Asset';
    }
}
