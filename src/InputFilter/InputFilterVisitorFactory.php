<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Psr\Container\ContainerInterface;

use function array_map;
use function usort;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class InputFilterVisitorFactory
{
    public function __invoke(ContainerInterface $container): InputFilterVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $inputVisitors = array_map(
        /** @param class-string<InputVisitorInterface> $name */
            static fn (string $name): InputVisitorInterface => $container->get($name),
            $config['laminas-form-shape']['input-visitors']
        );

        usort(
            $inputVisitors,
            static fn (InputVisitorInterface $visitor): int => (int) ($visitor instanceof InputVisitor)
        );

        return new InputFilterVisitor($inputVisitors);
    }
}
