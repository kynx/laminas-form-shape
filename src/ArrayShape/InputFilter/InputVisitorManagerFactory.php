<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 */
final readonly class InputVisitorManagerFactory
{
    public function __invoke(ContainerInterface $container): InputVisitorManager
    {
        /** @var FormCliConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $inputVisitors = array_map(
            /** @param class-string<InputVisitorInterface> $name */
            static fn (string $name): InputVisitorInterface => $container->get($name),
            $config['laminas-form-cli']['array-shape']['input-visitors']
        );

        return new InputVisitorManager($inputVisitors);
    }
}
