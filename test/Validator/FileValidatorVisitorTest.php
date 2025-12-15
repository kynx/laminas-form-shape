<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\FileValidatorVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Http\Message\UploadedFileInterface;

use function array_pop;
use function explode;

#[CoversClass(FileValidatorVisitor::class)]
final class FileValidatorVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'array'         => [
                new Crc32(),
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
                [
                    new TKeyedArray([
                        'name'     => new Union([new TString()]),
                        'tmp_name' => new Union([new TNonEmptyString()]),
                        'type'     => new Union([new TString()]),
                    ]),
                ],
            ],
            'uploaded file' => [
                new Crc32(),
                [new TNamedObject(UploadedFileInterface::class)],
                [new TNamedObject(UploadedFileInterface::class)],
            ],
        ];
    }

    #[DataProvider('defaultValidatorProvider')]
    public function testVisitSupportsDefaultValidators(ValidatorInterface $validator): void
    {
        $visitor  = self::getValidatorVisitor();
        $existing = new Union([new TArray([Type::getArrayKey(), Type::getMixed()])]);
        $expected = new Union([
            new TKeyedArray([
                'name'     => new Union([new TString()]),
                'tmp_name' => new Union([new TNonEmptyString()]),
                'type'     => new Union([new TString()]),
            ]),
        ]);
        self::fixUnionIds($expected->getAtomicTypes());

        $actual = $visitor->visit($validator, $existing);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, list{ValidatorInterface}>
     */
    public static function defaultValidatorProvider(): array
    {
        $tests = [];
        foreach (FileValidatorVisitor::DEFAULT_VALIDATORS as $validatorName) {
            $parts         = explode('\\', $validatorName);
            $class         = array_pop($parts);
            $tests[$class] = [new $validatorName([])];
        }

        return $tests;
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new FileValidatorVisitor();
    }
}
