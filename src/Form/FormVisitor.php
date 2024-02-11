<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Laminas\Form\FormInterface;
use Psalm\Type\Union;

final readonly class FormVisitor implements FormVisitorInterface
{
    public function __construct(private InputFilterVisitorInterface $inputFilterVisitor)
    {
    }

    public function visit(FormInterface $form): Union
    {
        // phpcs:disable SlevomatCodingStandard.Variables.UselessVariable.UselessVariable
        $union = $this->inputFilterVisitor->visit($form->getInputFilter());
        // phpcs:enable

        /**
         * Lots of weirdness to deal with here:
         *   * Validation groups - these are set on the inputfilter during validation :|
         *   * Collection::allowRemove
         *   * more...?
         */

        return $union;
    }
}
