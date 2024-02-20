<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use Composer\Autoload\ClassLoader;
use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormLocatorFactory
{
    public function __invoke(ContainerInterface $container): FormLocator
    {
        $loader = require 'vendor/autoload.php';
        assert($loader instanceof ClassLoader);

        if ($container->has(FormElementManager::class)) {
            $formElementManger = $container->get(FormElementManager::class);
        } else {
            $formElementManger = new FormElementManager($container);
        }

        return new FormLocator($loader, $formElementManger);
    }
}
