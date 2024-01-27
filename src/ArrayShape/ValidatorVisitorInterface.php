<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Laminas\Validator\ValidatorInterface;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
interface ValidatorVisitorInterface
{
    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    public function getTypes(ValidatorInterface $validator, array $existing): array;
}
