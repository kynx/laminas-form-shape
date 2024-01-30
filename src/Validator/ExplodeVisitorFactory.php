<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class ExplodeVisitorFactory
{
    public function __invoke(ContainerInterface $container): ExplodeVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var array{item-types?: list<PsalmType>} $visitorConfig */
        $visitorConfig = (array) ($config['laminas-form-shape']['validator']['explode'] ?? []);
        $itemTypes     = $visitorConfig['item-types'] ?? ExplodeVisitor::DEFAULT_ITEM_TYPES;

        $validatorVisitors = [];
        foreach ($config['laminas-form-shape']['validator-visitors'] ?? [] as $visitorName) {
            if ($visitorName === ExplodeVisitor::class) {
                continue;
            }

            $validatorVisitors[] = $this->getVisitor($container, $visitorName);
        }

        return new ExplodeVisitor($validatorVisitors, $itemTypes);
    }

    /**
     * @param class-string<ValidatorVisitorInterface> $visitorName
     */
    private function getVisitor(ContainerInterface $container, string $visitorName): ValidatorVisitorInterface
    {
        if ($container->has($visitorName)) {
            $visitor = $container->get($visitorName);
        } else {
            $visitor = new $visitorName();
        }

        assert($visitor instanceof ValidatorVisitorInterface);
        return $visitor;
    }
}
