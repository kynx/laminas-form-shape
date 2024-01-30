<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Date;
use Laminas\Validator\DateStep;
use Laminas\Validator\ValidatorInterface;

final readonly class DateStepVisitor implements ValidatorVisitorInterface
{
    private DateVisitor $dateVistor;
    public function __construct()
    {
        $this->dateVistor = new DateVisitor();
    }

    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof DateStep) {
            return $existing;
        }

        return $this->dateVistor->visit(new Date($validator->getOptions()), $existing);
    }
}
