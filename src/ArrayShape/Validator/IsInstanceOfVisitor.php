<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\ValidatorInterface;

final readonly class IsInstanceOfVisitor implements ValidatorVisitorInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof IsInstanceOf) {
            return $existing;
        }

        /** @psalm-suppress ArgumentTypeCoercion getClassName() returns `string` :( */
        return [new ClassString($validator->getClassName())];
    }
}
