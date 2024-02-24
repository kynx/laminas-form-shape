<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\CodingStandards;

use Kynx\Laminas\FormShape\CodingStandards\PhpCodeSnifferFixer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

#[CoversClass(PhpCodeSnifferFixer::class)]
final class PhpCodeSnifferFixerTest extends TestCase
{
    public function testFixProcessesPaths(): void
    {
        $expected = 'mock-fixer.php foo bar';
        $fixer    = new PhpCodeSnifferFixer(__DIR__ . '/Asset/mock-fixer.php');

        $fixer->addFile('foo');
        $fixer->addFile('bar');

        ob_start();
        $fixer->fix();
        $actual = ob_get_clean();

        self::assertSame($expected, $actual);
    }
}
