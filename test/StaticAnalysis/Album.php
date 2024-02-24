<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\StaticAnalysis;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;

final class Album extends Fieldset
{
    /**
     * @param int|null|string $name
     */
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        $this->add(new Hidden('id'));
        $this->add(new Text('title'));
        $this->add(new Select('genre', ['value_options' => [1 => 'Punk', 2 => 'Shlock']]));
        $this->add(new Number('chart_position', ['min' => 1, 'max' => 100]));
    }
}
