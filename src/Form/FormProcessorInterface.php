<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
interface FormProcessorInterface
{
    /**
     * @param array<string> $paths
     */
    public function process(
        array $paths,
        ProgressListenerInterface $listener,
        bool $processFieldsets = true,
        bool $removeGetDataReturn = true
    ): void;
}
