<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Kynx\Laminas\FormShape\Writer\FileWriter;
use Kynx\Laminas\FormShape\Writer\WriterException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;
use ReflectionClass;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(FileWriter::class)]
final class FileWriterTest extends TestCase
{
    private string $tempFile;
    private FileWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        $this->writer = new FileWriter(new TypeNamer('T{shortName}Array'), new PrettyPrinter());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->tempFile);
    }

    public function testWriteUnreadableFileThrowsException(): void
    {
        $original = <<<ORIGINAL
        <?php
        
        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Form;

        final class Unreadable extends Form
        {
        }
        ORIGINAL;

        $reflection = $this->getReflection('Unreadable', $original);
        $type       = new Union([new TInt()]);
        file_put_contents($this->tempFile, '');

        self::expectException(WriterException::class);
        self::expectExceptionMessage("Could not parse");
        $this->writer->write($reflection, $type, []);
    }

    public function testWriteNoClassThrowsException(): void
    {
        $original = <<<ORIGINAL
        <?php
        
        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Form;

        final class NoClass extends Form
        {
        }
        ORIGINAL;

        $reflection = $this->getReflection('NoClass', $original);
        $type       = new Union([new TInt()]);
        file_put_contents($this->tempFile, "<?php\n");

        self::expectException(WriterException::class);
        self::expectExceptionMessage("Could not find class");
        $this->writer->write($reflection, $type, []);
    }

    #[DataProvider('writeProvider')]
    public function testWrite(string $className, string $original, string $expected): void
    {
        $reflection = $this->getReflection($className, $original);
        $type       = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $type   = $this->writer->write($reflection, $type, [], true);
        $actual = file_get_contents($this->tempFile);
        self::assertSame($expected, $actual);
        self::assertSame("T{$className}Array", $type);
    }

    public static function writeProvider(): array
    {
        return [
            'fieldset'                               => [
                'MyFieldset',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Fieldset;

                final class MyFieldset extends Fieldset
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Fieldset;

                /**
                 * @psalm-type TMyFieldsetArray = array{
                 *     foo: int,
                 * }
                 */
                final class MyFieldset extends Fieldset
                {
                }

                EXPECTED,
            ],
            'no class docblock'                      => [
                'NoClassDocBlock',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                final class NoClassDocBlock extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type TNoClassDocBlockArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TNoClassDocBlockArray>
                 */
                final class NoClassDocBlock extends Form
                {
                }

                EXPECTED,
            ],
            'no class docblock with attribute'       => [
                'NoClassDocBlockWithAttribute',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                #[TestAttribute]
                final class NoClassDocBlockWithAttribute extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type TNoClassDocBlockWithAttributeArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TNoClassDocBlockWithAttributeArray>
                 */
                #[TestAttribute]
                final class NoClassDocBlockWithAttribute extends Form
                {
                }

                EXPECTED,
            ],
            'existing class docblock'                => [
                'ExistingClassDocBlock',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * Foo
                 *
                 * @internal
                 */
                final class ExistingClassDocBlock extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * Foo
                 *
                 * @internal
                 * @psalm-type TExistingClassDocBlockArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TExistingClassDocBlockArray>
                 */
                final class ExistingClassDocBlock extends Form
                {
                }

                EXPECTED,
            ],
            'existing class docblock with attribute' => [
                'ExistingClassDocBlockWithAttribute',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * Foo
                 *
                 * @internal
                 */
                #[TestAttribute]
                final class ExistingClassDocBlockWithAttribute extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * Foo
                 *
                 * @internal
                 * @psalm-type TExistingClassDocBlockWithAttributeArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TExistingClassDocBlockWithAttributeArray>
                 */
                #[TestAttribute]
                final class ExistingClassDocBlockWithAttribute extends Form
                {
                }

                EXPECTED,
            ],
            'getData not overridden'                 => [
                'GetDataNotOverridden',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                final class GetDataNotOverridden extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type TGetDataNotOverriddenArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TGetDataNotOverriddenArray>
                 */
                final class GetDataNotOverridden extends Form
                {
                }

                EXPECTED,
            ],
            'getData return type'                    => [
                'GetDataReturnType',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;

                final class GetDataReturnType extends Form
                {
                    /**
                     * @return Foo
                     */
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;

                /**
                 * @psalm-type TGetDataReturnTypeArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TGetDataReturnTypeArray>
                 */
                final class GetDataReturnType extends Form
                {
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }

                EXPECTED,
            ],
            'getData @method'                        => [
                'GetDataMethod',
                <<<ORIGINAL
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                /**
                 * @method array{foo: string} getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                 */
                final class GetDataMethod extends Form
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                /**
                 * @psalm-type TGetDataMethodArray = array{
                 *     foo: int,
                 * }
                 * @extends Form<TGetDataMethodArray>
                 */
                final class GetDataMethod extends Form
                {
                }
                
                EXPECTED,
            ],
        ];
    }

    #[DataProvider('importTypesProvider')]
    public function testWriteImportsTypes(string $className, string $original, string $expected): void
    {
        $reflection  = $this->getReflection($className, $original);
        $typeAlias   = new TTypeAlias(__NAMESPACE__ . '\\FooFieldset', 'TFoo');
        $unusedAlias = new TTypeAlias(__NAMESPACE__ . '\\Unused', 'TBar');
        $type        = new Union([
            new TKeyedArray([
                'foo' => new Union([$typeAlias]),
            ]),
        ]);
        $importTypes = [
            new ImportType($typeAlias, new Union([new TInt()])),
            new ImportType($unusedAlias, new Union([new TString()])),
        ];

        $this->writer->write($reflection, $type, $importTypes);
        $actual = file_get_contents($this->tempFile);
        self::assertSame($expected, $actual);
    }

    public static function importTypesProvider(): array
    {
        return [
            'import type'           => [
                'SimpleImport',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Fieldset;

                final class SimpleImport extends Fieldset
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use KynxTest\Laminas\FormShape\Writer\FooFieldset;
                use Laminas\Form\Fieldset;
                
                /**
                 * @psalm-import-type TFoo from FooFieldset
                 * @psalm-type TSimpleImportArray = array{
                 *     foo: TFoo,
                 * }
                 */
                final class SimpleImport extends Fieldset
                {
                }
                
                EXPECTED,
            ],
            'conflicting shortName' => [
                'ShortName',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use KynxTest\Laminas\FormShape\Another\FooFieldset;
                use Laminas\Form\Fieldset;

                final class ShortName extends Fieldset
                {
                }
                ORIGINAL,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use KynxTest\Laminas\FormShape\Another\FooFieldset;
                use KynxTest\Laminas\FormShape\Writer\FooFieldset as WriterFooFieldset;
                use Laminas\Form\Fieldset;
                
                /**
                 * @psalm-import-type TFoo from WriterFooFieldset
                 * @psalm-type TShortNameArray = array{
                 *     foo: TFoo,
                 * }
                 */
                final class ShortName extends Fieldset
                {
                }
                
                EXPECTED,
            ],
        ];
    }

    private function getReflection(string $className, string $contents): ReflectionClass
    {
        file_put_contents($this->tempFile, $contents);
        /** @psalm-suppress UnresolvableInclude */
        require $this->tempFile;

        /** @psalm-suppress ArgumentTypeCoercion */
        return new ReflectionClass(__NAMESPACE__ . "\\Asset\\$className");
    }
}
