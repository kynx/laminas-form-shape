<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Barcode;
use Laminas\Validator\BusinessIdentifierCode;
use Laminas\Validator\CreditCard;
use Laminas\Validator\Csrf;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\GpsPoint;
use Laminas\Validator\Hostname;
use Laminas\Validator\Iban;
use Laminas\Validator\Ip;
use Laminas\Validator\IsJsonString;
use Laminas\Validator\Timezone;
use Laminas\Validator\UndisclosedPassword;
use Laminas\Validator\Uri;
use Laminas\Validator\Uuid;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Union;

use function in_array;

final readonly class NonEmptyStringVisitor implements ValidatorVisitorInterface
{
    public const DEFAULT_VALIDATORS = [
        Barcode::class,
        BusinessIdentifierCode::class,
        CreditCard::class,
        Csrf::class,
        EmailAddress::class,
        GpsPoint::class,
        Hostname::class,
        Iban::class,
        Ip::class,
        IsJsonString::class,
        Timezone::class,
        UndisclosedPassword::class,
        Uri::class,
        Uuid::class,
    ];

    /**
     * @param list<class-string<ValidatorInterface>> $stringValidators
     */
    public function __construct(private array $stringValidators = self::DEFAULT_VALIDATORS)
    {
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! in_array($validator::class, $this->stringValidators, true)) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([new TNonEmptyString()]));
    }
}
