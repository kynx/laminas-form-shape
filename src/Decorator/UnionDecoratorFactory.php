<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
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
        $shapeConfig = $config['laminas-form-shape'];

        ConfigLoader::load($shapeConfig['max-string-length'] ?? null);

        return new UnionDecorator($shapeConfig['indent'], $shapeConfig['literal-limit']);
    }
}
