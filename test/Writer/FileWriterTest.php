<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\FileWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;

use function file_get_contents;

#[CoversClass(FileWriter::class)]
final class FileWriterTest extends TestCase
{
    use GetReflectionTrait;

    private FileWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTempFile();
        $this->writer = new FileWriter(new MockCodeGenerator());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->tearDownTempFile();
    }

    public function testWriteSavesFile(): void
    {
        $original     = <<<ORIGINAL
        <?php

        namespace KynxTest\Laminas\FormShape\Writer\Asset;

        use Laminas\Form\Fieldset;

        final class WriterTest extends Fieldset
        {
        }
        ORIGINAL;
        $expected     = $original . "\n// EDITED";
        $expectedType = 'TFoo';

        $reflection = $this->getReflection('WriterTest', $original);
        $type       = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $actual   = $this->writer->write($reflection, $type, []);
        $contents = file_get_contents($this->tempFile);
        self::assertStringContainsString($expected, $contents);
        self::assertSame($expectedType, $actual);
    }
}
