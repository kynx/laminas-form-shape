<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class InArrayVisitorFactory
{
    public function __invoke(ContainerInterface $container): InArrayVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config        = $container->get('config');
        $visitorConfig = (array) ($config['laminas-form-shape']['validator']['in-array'] ?? []);

        return new InArrayVisitor(
            (bool) ($visitorConfig['allow-empty-haystack'] ?? true),
            (int) ($visitorConfig['max-literals'] ?? InArrayVisitor::DEFAULT_MAX_LITERALS)
        );
    }
}
