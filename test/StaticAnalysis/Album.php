<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\StaticAnalysis;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * @psalm-type TAlbumData = array{
 *     id:     null|string,
 *     title:  non-empty-string,
 *     genre:  '1'|'2'|'3',
 *     rating: numeric-string,
 * }
 */
final class Album extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @param int|null|string $name
     */
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        $this->add(new Hidden('id'));
        $this->add(new Text('title'));
        $this->add(new Select('genre', ['value_options' => [1 => 'Good', 2 => 'Bad', 3 => "Ugly"]]));
        $this->add(new Number('rating', ['min' => 1, 'max' => 10]));
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'id'    => [
                'required'    => true,
                'allow_empty' => true,
            ],
            'title' => [
                'required' => true,
            ],
        ];
    }
}
