<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\CodeGeneratorInterface;
use Kynx\Laminas\FormShape\Writer\FileWriterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

use function file_get_contents;

#[CoversClass(FileWriterFactory::class)]
final class FileWriterFactoryTest extends TestCase
{
    use GetReflectionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTempFile();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tearDownTempFile();
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [CodeGeneratorInterface::class, new MockCodeGenerator()],
            ]);

        $factory  = new FileWriterFactory();
        $instance = $factory($container);

        $expected   = 'TFoo';
        $original   = <<<ORIGINAL
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Fieldset;

        final class WriterFactoryTest extends Fieldset
        {
        }
        ORIGINAL;
        $reflection = $this->getReflection('WriterFactoryTest', $original);
        $type       = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $actual   = $instance->write($reflection, $type, []);
        $contents = (string) file_get_contents($this->tempFile);
        self::assertStringContainsString('// EDITED', $contents);
        self::assertSame($expected, $actual);
    }
}
