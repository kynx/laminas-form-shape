<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
abstract readonly class AbstractInputVisitorFactory
{
    abstract public function __invoke(ContainerInterface $container): InputVisitorInterface;

    /**
     * @return array<FilterVisitorInterface>
     */
    protected function getFilterVisitors(ContainerInterface $container): array
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $filterVisitors = [];
        foreach ($config['laminas-form-shape']['filter-visitors'] as $visitorName) {
            $visitor          = $this->getVisitor($container, $visitorName);
            $filterVisitors[] = $visitor;
        }

        return $filterVisitors;
    }

    /**
     * @return array<ValidatorVisitorInterface>
     */
    protected function getValidatorVisitors(ContainerInterface $container): array
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $validatorVisitors = [];
        foreach ($config['laminas-form-shape']['validator-visitors'] as $visitorName) {
            $visitor             = $this->getVisitor($container, $visitorName);
            $validatorVisitors[] = $visitor;
        }

        return $validatorVisitors;
    }

    /**
     * @template T of FilterVisitorInterface|ValidatorVisitorInterface
     * @param class-string<T> $visitorName
     * @return T
     */
    private function getVisitor(
        ContainerInterface $container,
        string $visitorName
    ): FilterVisitorInterface|ValidatorVisitorInterface {
        if ($container->has($visitorName)) {
            return $container->get($visitorName);
        }

        return new $visitorName();
    }
}
