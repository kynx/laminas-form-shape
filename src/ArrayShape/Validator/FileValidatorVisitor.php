<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
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

    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! in_array($validator::class, $this->fileValidators, true)) {
            return $existing;
        }

        $existing = PsalmType::replaceArrayTypes($existing, [
            new Generic(
                PsalmType::NonEmptyArray,
                [PsalmType::NonEmptyString]
            ),
        ]);
        $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return PsalmType::filter($existing, [
            PsalmType::NonEmptyArray,
            PsalmType::NonEmptyString,
            new ClassString(UploadedFileInterface::class),
        ]);
    }
}
