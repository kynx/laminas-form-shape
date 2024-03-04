<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psr\Container\ContainerInterface;

use function array_merge;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class FileInputVisitorFactory extends AbstractInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): FileInputVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var array<string, bool> $inputConfig */
        $inputConfig = $config['laminas-form-shape']['input']['file'] ?? [];
        $fileConfig  = array_merge(['laminas' => true, 'psr-7' => true], $inputConfig);

        $style = FileInputStyle::Both;
        if ($fileConfig['laminas'] && ! $fileConfig['psr-7']) {
            $style = FileInputStyle::Laminas;
        } elseif (! $fileConfig['laminas'] && $fileConfig['psr-7']) {
            $style = FileInputStyle::Psr7;
        }

        return new FileInputVisitor(
            $style,
            $this->getFilterVisitors($container),
            $this->getValidatorVisitors($container),
        );
    }
}
