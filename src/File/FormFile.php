<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\File;

use Laminas\Form\FormInterface;
use Nette\PhpGenerator\PhpFile;

final readonly class FormFile
{
    public function __construct(public string $fileName, public PhpFile $file, public FormInterface $form)
    {
    }
}
