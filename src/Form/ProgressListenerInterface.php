<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use ReflectionClass;

interface ProgressListenerInterface
{
    public function error(string $error): void;

    public function success(ReflectionClass $reflection): void;

    public function finally(int $processed): void;
}
