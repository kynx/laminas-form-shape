<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

interface FormLocatorInterface
{
    /**
     * @param array<string> $paths
     * @return list<FormFile>
     */
    public function locate(array $paths): array;
}
