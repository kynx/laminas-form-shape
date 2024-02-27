<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\InputFilter\InputInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class InputVisitor extends AbstractInputVisitor
{
    public function visit(InputInterface $input): Union
    {
        $initial = new Union([new TNull(), new TString()]);
        $union   = $this->visitInput($input, $initial);

        if ($this->hasFallback($input)) {
            $union = Type::combineUnionTypes($union, TypeUtil::toStrictUnion($input->getFallbackValue()));
        }

        if ($union->getAtomicTypes() === []) {
            throw InputVisitorException::cannotGetInputType($input);
        }

        return $union;
    }
}
