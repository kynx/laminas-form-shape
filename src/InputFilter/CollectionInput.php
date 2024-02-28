<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Laminas\InputFilter\ArrayInput;

/**
 * Specialised input for representing collections with non-fieldset target elements
 *
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final class CollectionInput extends ArrayInput
{
    private int $count = 0;

    public function setCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
