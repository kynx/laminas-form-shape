<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

use function array_map;
use function assert;
use function is_a;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
trait ValidatorVisitorFactoryTrait
{
    /**
     * @return array<ValidatorVisitorInterface>
     */
    protected function getValidatorVisitors(ContainerInterface $container): array
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];

        return array_map(
            static fn (string $name): ValidatorVisitorInterface => self::getValidatorVisitor($container, $name),
            $config['laminas-form-shape']['validator-visitors'] ?? []
        );
    }

    /**
     * @param class-string $visitorName
     */
    private static function getValidatorVisitor(
        ContainerInterface $container,
        string $visitorName
    ): ValidatorVisitorInterface {
        if (! is_a($visitorName, ValidatorVisitorInterface::class, true)) {
            throw InvalidValidatorConfigurationException::forVisitor($visitorName);
        }

        if ($container->has($visitorName)) {
            $visitor = $container->get($visitorName);
        } else {
            $visitor = new $visitorName();
        }

        assert($visitor instanceof ValidatorVisitorInterface);
        return $visitor;
    }
}
