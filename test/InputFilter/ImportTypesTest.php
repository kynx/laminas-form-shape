<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

#[CoversClass(ImportTypes::class)]
final class ImportTypesTest extends TestCase
{
    public function testGetReturnsImportType(): void
    {
        $expected    = new ImportType(
            new TTypeAlias(self::class, 'TFoo'),
            new Union([new TInt()])
        );
        $importTypes = new ImportTypes(['foo' => $expected]);

        $actual = $importTypes->get('foo');
        self::assertSame($expected, $actual);
    }

    public function testGetReturnsNestedTypes(): void
    {
        $expected    = new ImportType(
            new TTypeAlias(self::class, 'TFoo'),
            new Union([new TInt()])
        );
        $importTypes = new ImportTypes(['foo' => ['bar' => $expected]]);

        $nestedTypes = $importTypes->get('foo');
        self::assertInstanceOf(ImportTypes::class, $nestedTypes);
        $actual = $nestedTypes->get('bar');
        self::assertSame($expected, $actual);
    }

    public function testGetReturnsEmptyTypes(): void
    {
        $expected    = new ImportTypes([]);
        $importTypes = new ImportTypes([]);

        $actual = $importTypes->get('foo');
        self::assertEquals($expected, $actual);
    }
}
