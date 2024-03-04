<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\FileInputVisitorFactory;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitor;
use Laminas\InputFilter\FileInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;

#[CoversClass(FileInputVisitorFactory::class)]
final class FileInputVisitorFactoryTest extends TestCase
{
    /**
     * @param non-empty-array<Atomic> $expected
     */
    #[DataProvider('configProvider')]
    public function testInvokeConfiguresStyle(array $config, array $expected): void
    {
        $expected  = new Union($expected);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig($config)],
            ]);

        $factory  = new FileInputVisitorFactory();
        $instance = $factory($container);

        $actual = $instance->visit(new FileInput('foo'));
        self::assertEquals($expected, $actual);
    }

    public static function configProvider(): array
    {
        $laminas = FileValidatorVisitor::getUploadArray();
        $psr7    = new TNamedObject(UploadedFileInterface::class);

        return [
            'laminas' => [['laminas' => true, 'psr-7' => false], [$laminas]],
            'psr-7'   => [['laminas' => false, 'psr-7' => true], [$psr7]],
            'both'    => [['laminas' => true, 'psr-7' => true], [$laminas, $psr7]],
        ];
    }

    private function getConfig(array $config): array
    {
        return [
            'laminas-form-shape' => [
                'filter-visitors'    => [],
                'validator-visitors' => [
                    FileValidatorVisitor::class,
                ],
                'input'              => [
                    'file' => $config,
                ],
            ],
        ];
    }
}
