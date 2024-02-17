<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use Laminas\Form\FormInterface;
use ReflectionClass;

final readonly class FormFile
{
    /**
     * @param ReflectionClass<FormInterface> $reflection
     */
    public function __construct(public ReflectionClass $reflection, public FormInterface $form)
    {
    }
}
