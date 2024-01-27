<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 */
final readonly class InArrayVisitorFactory
{
    public function __invoke(ContainerInterface $container): InArrayVisitor
    {
        /** @var FormCliConfigurationArray $config */
        $config        = $container->get('config');
        $visitorConfig = (array) ($config['laminas-form-cli']['array-shape']['validator']['in-array'] ?? []);

        return new InArrayVisitor(
            (bool) ($visitorConfig['allow-empty-haystack'] ?? true),
            (int) ($visitorConfig['max-literals'] ?? InArrayVisitor::DEFAULT_MAX_LITERALS)
        );
    }
}
