<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class TypeNamerFactory
{
    public function __invoke(ContainerInterface $container): TypeNamer
    {
        /** @var FormShapeConfigurationArray $config */
        $config      = $container->get('config') ?? [];
        $shapeConfig = $config['laminas-form-shape'];

        return new TypeNamer($shapeConfig['type-name-template']);
    }
}
