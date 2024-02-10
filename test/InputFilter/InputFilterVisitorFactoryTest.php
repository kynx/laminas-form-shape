<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
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

        $expected    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));

        $actual = $instance->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }
}
