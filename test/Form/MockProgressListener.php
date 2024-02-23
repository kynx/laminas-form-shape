<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Form\ProgressListenerInterface;
use ReflectionClass;

final class MockProgressListener implements ProgressListenerInterface
{
    /** @var list<string> */
    public array $errors = [];
    /** @var list<ReflectionClass> */
    public array $success  = [];
    public ?int $processed = null;

    public function error(string $error): void
    {
        $this->errors[] = $error;
    }

    public function success(ReflectionClass $reflection): void
    {
        $this->success[] = $reflection;
    }

    public function finally(int $processed): void
    {
        $this->processed = $processed;
    }
}
