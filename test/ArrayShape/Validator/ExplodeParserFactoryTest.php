<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(ExplodeParserFactory::class)]
final class ExplodeParserFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig(['item-types' => [PsalmType::String]], [DigitsParser::class]);
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

    public function testInvokeGetsValidatorFromContainer(): void
    {
        $config    = $this->getConfig(['item-types' => [PsalmType::String]], [ValidatorParserInterface::class]);
        $validatorParser = $this->createStub(ValidatorParserInterface::class);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [ValidatorParserInterface::class, $validatorParser]
            ]);

        $factory  = new ExplodeParserFactory();
        $instance = $factory($container);

        $validatorParser->method('getTypes')
            ->willReturn([PsalmType::Bool]);
        $validator = $this->createStub(ValidatorInterface::class);
        $types     = $instance->getTypes(new Explode(['validator' => $validator]), [PsalmType::Int]);
        self::assertSame([PsalmType::Bool], $types);
    }

    private function getConfig(array $parserConfig, array $parsers): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator'         => [
                        'explode' => $parserConfig,
                    ],
                    'validator-parsers' => $parsers,
                ],
            ],
        ];
    }
}
