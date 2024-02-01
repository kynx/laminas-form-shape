<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\Psalm\Config;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class UnionDecoratorFactory
{
    public function __invoke(ContainerInterface $container): UnionDecorator
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        Config::initDecoratorConfig($config['laminas-form-shape']['max-string-length']);

        return new UnionDecorator($config['laminas-form-shape']['indent']);
    }
}
