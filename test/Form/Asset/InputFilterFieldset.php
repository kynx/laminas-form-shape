<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form\Asset;

use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

final class InputFilterFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        $this->add(new Text('first'));
        $this->add(new Text('second'));
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'second' => [
                'required' => true,
            ],
        ];
    }
}