<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\CollectionInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionInput::class)]
final class CollectionInputTest extends TestCase
{
    public function testSetCount(): void
    {
        $expected = 1;
        $input    = new CollectionInput();
        $input->setCount($expected);
        $actual = $input->getCount();
        self::assertSame($expected, $actual);
    }
}
