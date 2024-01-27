<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
use Laminas\InputFilter\InputInterface;

final readonly class InputVisitorManager
{
    /**
     * @param array<class-string<InputInterface>, InputVisitorInterface> $inputVisitors
     */
    public function __construct(private array $inputVisitors)
    {
    }

    public function getVisitor(InputInterface $input): InputVisitorInterface
    {
        if (isset($this->inputVisitors[$input::class])) {
            return $this->inputVisitors[$input::class];
        }

        throw ArrayShapeException::noVisitorForInput($input);
    }
}
