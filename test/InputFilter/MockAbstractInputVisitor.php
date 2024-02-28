<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\AbstractInputVisitor;
use Laminas\InputFilter\InputInterface;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class MockAbstractInputVisitor extends AbstractInputVisitor
{
    public function visit(InputInterface $input): ?Union
    {
        return $this->visitInput($input, new Union([new TNull(), new TString()]));
    }
}
