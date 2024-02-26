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
        $importTypes = new ImportTypes($expected);

        $actual = $importTypes->get();
        self::assertSame($expected, $actual);
    }

    public function testGetChildrenReturnsNestedTypes(): void
    {
        $expected    = new ImportTypes(new ImportType(
            new TTypeAlias(self::class, 'TFoo'),
            new Union([new TInt()])
        ));
        $importTypes = new ImportTypes(null, [
            'foo' => new ImportTypes(null, [
                'bar' => $expected,
            ]),
        ]);

        $nestedTypes = $importTypes->getChildren('foo');
        self::assertInstanceOf(ImportTypes::class, $nestedTypes);
        $actual = $nestedTypes->getChildren('bar');
        self::assertSame($expected, $actual);
    }

    public function testGetReturnsEmptyTypes(): void
    {
        $expected    = new ImportTypes();
        $importTypes = new ImportTypes();

        $actual = $importTypes->getChildren('foo');
        self::assertEquals($expected, $actual);
    }
}
