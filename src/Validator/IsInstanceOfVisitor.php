<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\ClassString;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\ValidatorInterface;

final readonly class IsInstanceOfVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof IsInstanceOf) {
            return $existing;
        }

        /** @psalm-suppress ArgumentTypeCoercion getClassName() returns `string` :( */
        return [new ClassString($validator->getClassName())];
    }
}
