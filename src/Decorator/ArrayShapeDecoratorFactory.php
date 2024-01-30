<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class ArrayShapeDecoratorFactory
{
    public function __invoke(ContainerInterface $container): ArrayShapeDecorator
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        return new ArrayShapeDecorator($config['laminas-form-shape']['indent']);
    }
}
