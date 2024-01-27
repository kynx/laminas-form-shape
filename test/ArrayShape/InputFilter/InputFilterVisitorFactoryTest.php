<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormCli\ArrayShape\Type\ArrayType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(InputFilterVisitorFactory::class)]
final class InputFilterVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $inputVisitorManager = new InputVisitorManager([Input::class => new InputVisitor([], [])]);
        $container           = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [InputVisitorManager::class, $inputVisitorManager],
            ]);

        $factory  = new InputFilterVisitorFactory();
        $instance = $factory($container);

        $expected    = new ArrayType('', [new InputType('foo', [PsalmType::String])], false);
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));

        $actual = $instance->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }
}
