<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
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

        $expected    = new InputFilterShape('', [
            new InputShape('foo', [PsalmType::Null, PsalmType::String]),
        ], false);
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));

        $actual = $instance->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }
}
