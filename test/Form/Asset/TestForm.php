<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form\Asset;

use Laminas\Form\Element\Text;
use Laminas\Form\Form;

final class TestForm extends Form
{
    public function init(): void
    {
        $this->add([
            'name' => 'test',
            'type' => Text::class,
        ]);
    }
}
