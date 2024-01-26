<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeParserFactory;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeParserFactory
 */
final class ExplodeParserFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator'         => [
                        'explode' => [
                            'item-types' => [PsalmType::String],
                        ],
                    ],
                    'validator-parsers' => [
                        DigitsParser::class,
                    ],
                ],
            ],
        ];
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new ExplodeParserFactory();
        $instance = $factory($container);

        $validator = new Explode(['validator' => new Digits()]);
        $types     = $instance->getTypes($validator, [PsalmType::String]);
        self::assertSame([PsalmType::String, PsalmType::NumericString], $types);
    }
}
