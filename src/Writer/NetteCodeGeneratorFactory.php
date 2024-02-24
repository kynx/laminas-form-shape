<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class NetteCodeGeneratorFactory
{
    public function __invoke(ContainerInterface $container): NetteCodeGenerator
    {
        return new NetteCodeGenerator(
            $container->get(TypeNamerInterface::class),
            $container->get(DecoratorInterface::class)
        );
    }
}
