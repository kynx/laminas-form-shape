<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class InputVisitorManagerFactory
{
    public function __invoke(ContainerInterface $container): InputVisitorManager
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $inputVisitors = array_map(
            /** @param class-string<InputVisitorInterface> $name */
            static fn (string $name): InputVisitorInterface => $container->get($name),
            $config['laminas-form-shape']['input-visitors']
        );

        return new InputVisitorManager($inputVisitors);
    }
}
