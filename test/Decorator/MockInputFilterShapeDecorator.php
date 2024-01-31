<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\AbstractInputFilterShapeDecorator;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;

final readonly class MockInputFilterShapeDecorator extends AbstractInputFilterShapeDecorator
{
    public function getTypeName(InputFilterShape|InputShape $shape): string
    {
        return parent::getTypeName($shape);
    }
}
