<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\DocBlock;
use Kynx\Laminas\FormShape\Writer\FileWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function explode;
use function file_get_contents;
use function file_put_contents;
use function implode;
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
        $this->writer   = new FileWriter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->tempFile);
    }

    #[DataProvider('writeProvider')]
    public function testWrite(
        string $className,
        string $original,
        string $classDocBlock,
        ?string $getDataDocBlock,
        string $expected
    ): void {
        $reflection      = $this->getReflection($className, $original);
        $classDocBlock   = DocBlock::fromDocComment($classDocBlock);
        $getDataDocBlock = $getDataDocBlock === null ? null : DocBlock::fromDocComment($getDataDocBlock);

        $this->writer->write($reflection, $classDocBlock, $getDataDocBlock);
        $actual = file_get_contents($this->tempFile);
        self::assertSame($expected, $actual);
    }

    public static function writeProvider(): array
    {
        return [
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
                <<<CLASS_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                CLASS_DOCBLOCK,
                null,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type Foo = array{bar: int}
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
                <<<CLASS_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                CLASS_DOCBLOCK,
                null,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type Foo = array{bar: int}
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
                <<<CLASS_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                CLASS_DOCBLOCK,
                null,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type Foo = array{bar: int}
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
                <<<CLASS_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                CLASS_DOCBLOCK,
                null,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                #[TestAttribute]
                final class ExistingClassDocBlockWithAttribute extends Form
                {
                }
                EXPECTED,
            ],
            'no getData method'                      => [
                'NoGetDataMethod',
                <<<ORIGINAL
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                final class NoGetDataMethod
                {
                }
                ORIGINAL,
                '',
                <<<GETDATA_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                GETDATA_DOCBLOCK,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                final class NoGetDataMethod
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
                '',
                <<<GETDATA_DOCBLOCK
                /**
                 * @psalm-type Foo = array{bar: int}
                 */
                GETDATA_DOCBLOCK,
                <<<EXPECTED
                <?php

                namespace KynxTest\Laminas\FormShape\Writer\Asset;

                use Laminas\Form\Form;

                final class GetDataNotOverridden extends Form
                {
                }
                EXPECTED,
            ],
            'getData no existing docblock'           => [
                'GetDataNoExisting',
                <<<ORIGINAL
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                final class GetDataNoExisting extends Form
                {
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }
                ORIGINAL,
                '',
                <<<GETDATA_DOCBLOCK
                /**
                 * @return Foo
                 */
                GETDATA_DOCBLOCK,
                <<<EXPECTED
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                final class GetDataNoExisting extends Form
                {
                    /**
                     * @return Foo
                     */
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }
                EXPECTED,
            ],
            'getData remove docblock'                => [
                'GetDataRemoveDocBlock',
                <<<ORIGINAL
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                final class GetDataRemoveDocBlock extends Form
                {
                    /**
                     * @return array{foo: int}
                     */
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }
                ORIGINAL,
                '',
                '',
                <<<EXPECTED
                <?php
                
                namespace KynxTest\Laminas\FormShape\Writer\Asset;
                
                use Laminas\Form\Form;
                use Laminas\Form\FormInterface;
                
                final class GetDataRemoveDocBlock extends Form
                {
                    public function getData(int \$flag = FormInterface::VALUES_NORMALIZED)
                    {
                    }
                }
                EXPECTED,
            ],
        ];
    }

    public function testWritePreservesExistingLineEndings(): void
    {
        $original = <<<ORIGINAL
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Form;

        final class PreserveLineEndings extends Form
        {
        }
        ORIGINAL;
        $docBlock = <<<DOCBLOCK
        /**
         * @psalm-type Foo = array{foo: int}
         */
        DOCBLOCK;
        $expected = <<<EXPECTED
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Form;

        $docBlock
        final class PreserveLineEndings extends Form
        {
        }
        EXPECTED;

        $original = implode("\r\n", explode("\n", $original));
        $expected = implode("\r\n", explode("\n", $expected));

        $reflection    = $this->getReflection('PreserveLineEndings', $original);
        $classDocBlock = DocBlock::fromDocComment($docBlock);

        $this->writer->write($reflection, $classDocBlock, null);
        $actual = file_get_contents($this->tempFile);
        self::assertSame($expected, $actual);
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
