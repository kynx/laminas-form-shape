<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

interface FormLocatorInterface
{
    /**
     * Returns an array of `FormFile` instances found in given `$paths`
     *
     * @param array<string> $paths
     * @return list<FormFile>
     */
    public function locate(array $paths): array;
}
