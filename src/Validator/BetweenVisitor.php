<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

use function is_int;
use function is_numeric;

final readonly class BetweenVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Between) {
            return $previous;
        }

        /** @var mixed $min */
        $min = $validator->getMin();
        /** @var mixed $max */
        $max = $validator->getMax();

        if (is_int($min) && is_int($max)) {
            $narrow = new Union([new TIntRange($min, $max), new TFloat(), new TNumericString()]);
        } elseif (is_numeric($min) && is_numeric($max)) {
            $narrow = new Union([new TInt(), new TFloat(), new TNumericString()]);
        } else {
            $narrow = new Union([new TString()]);
        }

        return TypeUtil::narrow($previous, $narrow);
    }
}
