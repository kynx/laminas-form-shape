<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;

final readonly class IsInstanceOfVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof IsInstanceOf) {
            return $previous;
        }

        /** @psalm-suppress ArgumentTypeCoercion getClassName() returns `string` :( */
        $classString = new TNamedObject($validator->getClassName());

        return TypeUtil::narrow($previous, new Union([$classString]));
    }
}
