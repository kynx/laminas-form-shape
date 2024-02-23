<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;

use function assert;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FieldsetVisitor
{
    public function __construct(private FormVisitor $formVisitor)
    {
    }

    /**
     * @param array<ImportType> $importTypes
     */
    public function visit(FieldsetInterface $fieldset, array $importTypes): Union
    {
        $clone = clone $fieldset;
        $clone->setName('visit');
        $form = new Form();
        $form->add($clone);

        $formUnion  = $this->formVisitor->visit($form, $importTypes);
        $keyedArray = $formUnion->getSingleAtomic();
        assert($keyedArray instanceof TKeyedArray && isset($keyedArray->properties['visit']));

        return $keyedArray->properties['visit'];
    }
}
