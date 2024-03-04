<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

enum FileInputStyle
{
    case Laminas;
    case Psr7;
    case Both;
}
