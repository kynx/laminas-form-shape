<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class InputFilterShapeDecoratorFactory
{
    public function __invoke(ContainerInterface $container): InputFilterShapeDecorator
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        return new InputFilterShapeDecorator($config['laminas-form-shape']['indent']);
    }
}
