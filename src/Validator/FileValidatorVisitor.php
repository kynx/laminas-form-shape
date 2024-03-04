<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\File\ExcludeMimeType;
use Laminas\Validator\File\Exists;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\Hash;
use Laminas\Validator\File\ImageSize;
use Laminas\Validator\File\IsCompressed;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Md5;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Sha1;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\File\WordCount;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Http\Message\UploadedFileInterface;

use function in_array;

final readonly class FileValidatorVisitor implements ValidatorVisitorInterface
{
    public const DEFAULT_VALIDATORS = [
        Crc32::class,
        ExcludeMimeType::class,
        Exists::class,
        FilesSize::class,
        Hash::class,
        ImageSize::class,
        IsCompressed::class,
        IsImage::class,
        Md5::class,
        MimeType::class,
        Sha1::class,
        Size::class,
        UploadFile::class,
        WordCount::class,
    ];

    /**
     * @param list<class-string<ValidatorInterface>> $fileValidators
     */
    public function __construct(private array $fileValidators = self::DEFAULT_VALIDATORS)
    {
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! in_array($validator::class, $this->fileValidators, true)) {
            return $previous;
        }

        return TypeUtil::narrow($previous, self::getUploadUnion());
    }

    public static function getUploadUnion(): Union
    {
        return new Union([
            self::getUploadArray(),
            new TNamedObject(UploadedFileInterface::class),
        ]);
    }

    public static function getUploadArray(): TKeyedArray
    {
        return new TKeyedArray([
            'name'     => new Union([new TString()]),
            'tmp_name' => new Union([new TNonEmptyString()]),
            'type'     => new Union([new TString()]),
        ]);
    }
}
