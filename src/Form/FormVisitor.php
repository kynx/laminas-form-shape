<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\Form\FormInterface;

final readonly class FormVisitor implements FormVisitorInterface
{
    public function __construct(private InputFilterVisitorInterface $inputFilterVisitor)
    {
    }

    public function visit(FormInterface $form): CollectionFilterShape|InputFilterShape
    {
        // phpcs:disable SlevomatCodingStandard.Variables.UselessVariable.UselessVariable
        $shape = $this->inputFilterVisitor->visit($form->getInputFilter());
        // phpcs:enable

        /**
         * Lots of weirdness to deal with here:
         *   * Validation groups - these are set on the inputfilter during validation :|
         *   * Collection::allowRemove
         *   * more...?
         */

        return $shape;
    }
}
