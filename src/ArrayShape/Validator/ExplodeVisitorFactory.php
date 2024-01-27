<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 */
final readonly class ExplodeVisitorFactory
{
    public function __invoke(ContainerInterface $container): ExplodeVisitor
    {
        /** @var FormCliConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var array{item-types?: list<PsalmType>} $visitorConfig */
        $visitorConfig = (array) ($config['laminas-form-cli']['array-shape']['validator']['explode'] ?? []);
        $itemTypes     = $visitorConfig['item-types'] ?? ExplodeVisitor::DEFAULT_ITEM_TYPES;

        $validatorVisitors = [];
        foreach ($config['laminas-form-cli']['array-shape']['validator-visitors'] ?? [] as $visitorName) {
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
