<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

/** @psalm-import-type FormShapeConfigurationArray from ConfigProvider */
final readonly class AllowListVisitorFactory
{
    public function __invoke(ContainerInterface $container): AllowListVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config     = $container->get('config');
        $allowEmpty = (bool) ($config['laminas-form-shape']['filter']['allow-list']['allow-empty-list'] ?? true);

        return new AllowListVisitor($allowEmpty);
    }
}
