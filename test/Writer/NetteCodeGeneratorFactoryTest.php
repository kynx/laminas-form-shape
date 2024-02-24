<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Kynx\Laminas\FormShape\Writer\NetteCodeGeneratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(NetteCodeGeneratorFactory::class)]
final class NetteCodeGeneratorFactoryTest extends TestCase
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
                [TypeNamerInterface::class, new TypeNamer('T{shortName}Array')],
                [DecoratorInterface::class, new PrettyPrinter()],
            ]);

        $factory  = new NetteCodeGeneratorFactory();
        $instance = $factory($container);

        $expected   = '@psalm-type TCodeGeneratorFactoryTestArray';
        $original   = <<<ORIGINAL
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Fieldset;

        final class CodeGeneratorFactoryTest extends Fieldset
        {
        }
        ORIGINAL;
        $reflection = $this->getReflection('CodeGeneratorFactoryTest', $original);
        $type       = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $generated = $instance->generate($reflection, $type, [], $original);
        self::assertStringContainsString($expected, $generated->contents);
    }
}
