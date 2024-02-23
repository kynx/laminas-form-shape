<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormVisitorFactory
{
    public function __invoke(ContainerInterface $container): FormVisitor
    {
        return new FormVisitor($container->get(InputFilterVisitorInterface::class));
    }
}
