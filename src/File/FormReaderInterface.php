<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\File;

interface FormReaderInterface
{
    public function getFormFile(string $path): ?FormFile;
}
