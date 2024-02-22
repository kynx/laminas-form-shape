<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Kynx\Laminas\FormShape\Writer\FileWriterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;
use ReflectionClass;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(FileWriterFactory::class)]
final class FileWriterFactoryTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpunit_');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->tempFile);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [TypeNamerInterface::class, new TypeNamer('T{shortName}Array')],
                [DecoratorInterface::class, new PrettyPrinter()],
            ]);

        $factory  = new FileWriterFactory();
        $instance = $factory($container);

        $expected   = '@psalm-type TFactoryTestArray';
        $original   = <<<ORIGINAL
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Fieldset;

        final class FactoryTest extends Fieldset
        {
        }
        ORIGINAL;
        $reflection = $this->getReflection('FactoryTest', $original);
        $type       = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $instance->write($reflection, $type, []);
        $actual = file_get_contents($this->tempFile);
        self::assertStringContainsString($expected, $actual);
    }

    private function getReflection(string $className, string $contents): ReflectionClass
    {
        file_put_contents($this->tempFile, $contents);
        /** @psalm-suppress UnresolvableInclude */
        require $this->tempFile;

        /** @psalm-suppress ArgumentTypeCoercion */
        return new ReflectionClass(__NAMESPACE__ . "\\Asset\\$className");
    }
}
