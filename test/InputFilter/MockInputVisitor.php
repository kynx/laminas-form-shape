<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\AbstractInputVisitor;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\InputFilter\InputInterface;
use Psalm\Type\Union;

final readonly class MockInputVisitor extends AbstractInputVisitor
{
    public function getFilterVisitors(): array
    {
        return $this->filterVisitors;
    }

    public function getValidatorVisitors(): array
    {
        return $this->validatorVisitors;
    }

    public function visit(InputInterface $input): ?Union
    {
        return TypeUtil::getEmptyUnion();
    }
}
