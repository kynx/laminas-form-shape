<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class InputVisitorFactory
{
    public function __invoke(ContainerInterface $container): InputVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        $filterVisitors = [];
        foreach ($config['laminas-form-shape']['filter-visitors'] as $visitorName) {
            $visitor = $this->getVisitor($container, $visitorName);
            assert($visitor instanceof FilterVisitorInterface);
            $filterVisitors[] = $visitor;
        }

        $validatorVisitors = [];
        foreach ($config['laminas-form-shape']['validator-visitors'] as $visitorName) {
            $visitor = $this->getVisitor($container, $visitorName);
            assert($visitor instanceof ValidatorVisitorInterface);
            $validatorVisitors[] = $visitor;
        }

        return new InputVisitor($filterVisitors, $validatorVisitors);
    }

    /**
     * @param class-string<FilterVisitorInterface|ValidatorVisitorInterface> $visitorName
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
