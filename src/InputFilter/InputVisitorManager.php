<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\InputVisitorInterface;
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

        throw InputVisitorException::noVisitorForInput($input);
    }
}
