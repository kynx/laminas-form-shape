<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
interface FormLocatorInterface
{
    /**
     * @param array<string> $paths
     * @return array<FormFile>
     */
    public function locate(array $paths): array;
}
