<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

final readonly class ImportType
{
    public function __construct(public TTypeAlias $type, public Union $union)
    {
    }
}
