<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Date;
use Laminas\Validator\DateStep;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Union;

final readonly class DateStepVisitor implements ValidatorVisitorInterface
{
    private DateVisitor $dateVistor;
    public function __construct()
    {
        $this->dateVistor = new DateVisitor();
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof DateStep) {
            return $previous;
        }

        return $this->dateVistor->visit(new Date($validator->getOptions()), $previous);
    }
}
