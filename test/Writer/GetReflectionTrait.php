<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use ReflectionClass;

use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

trait GetReflectionTrait
{
    protected string $tempFile;

    protected function setUpTempFile(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpunit_');
    }

    protected function tearDownTempFile(): void
    {
        @unlink($this->tempFile);
    }

    protected function getReflection(string $className, string $contents): ReflectionClass
    {
        file_put_contents($this->tempFile, $contents);
        /** @psalm-suppress UnresolvableInclude */
        require $this->tempFile;

        /** @psalm-suppress ArgumentTypeCoercion */
        return new ReflectionClass(__NAMESPACE__ . "\\Asset\\$className");
    }
}
