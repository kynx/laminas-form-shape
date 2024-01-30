<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\AbstractDecorator;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;

final readonly class MockDecorator extends AbstractDecorator
{
    public function getTypeName(ArrayShape|ElementShape $shape): string
    {
        return parent::getTypeName($shape);
    }
}
