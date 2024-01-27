<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorVisitor;
use Laminas\Validator\Barcode;
use Laminas\Validator\BusinessIdentifierCode;
use Laminas\Validator\CreditCard;
use Laminas\Validator\Csrf;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\GpsPoint;
use Laminas\Validator\Hostname;
use Laminas\Validator\Iban;
use Laminas\Validator\Ip;
use Laminas\Validator\IsCountable;
use Laminas\Validator\IsJsonString;
use Laminas\Validator\UndisclosedPassword;
use Laminas\Validator\Uri;
use Laminas\Validator\Uuid;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

use function array_values;

#[CoversClass(StringValidatorVisitor::class)]
final class StringValidatorVisitorTest extends TestCase
{
    #[DataProvider('getTypesProvider')]
    public function testGetTypes(ValidatorInterface $validator, array $expected): void
    {
        $visitor = new StringValidatorVisitor();
        $actual  = $visitor->getTypes($validator, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, array_values($actual));
    }

    public static function getTypesProvider(): array
    {
        $httpClient     = self::createStub(ClientInterface::class);
        $requestFactory = self::createStub(RequestFactoryInterface::class);

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'              => [new IsCountable(), [PsalmType::String, PsalmType::Null]],
            'barcode'              => [new Barcode(), [PsalmType::NonEmptyString]],
            'business identifier'  => [new BusinessIdentifierCode(), [PsalmType::NonEmptyString]],
            'credit card'          => [new CreditCard(), [PsalmType::NonEmptyString]],
            'csrf'                 => [new Csrf(), [PsalmType::NonEmptyString]],
            'email'                => [new EmailAddress(), [PsalmType::NonEmptyString]],
            'gps point'            => [new GpsPoint(), [PsalmType::NonEmptyString]],
            'hostname'             => [new Hostname(), [PsalmType::NonEmptyString]],
            'iban'                 => [new Iban(), [PsalmType::NonEmptyString]],
            'ip'                   => [new Ip(), [PsalmType::NonEmptyString]],
            'json string'          => [new IsJsonString(), [PsalmType::NonEmptyString]],
            'undisclosed password' => [new UndisclosedPassword($httpClient, $requestFactory), [PsalmType::NonEmptyString]],
            'uri'                  => [new Uri(), [PsalmType::NonEmptyString]],
            'uuid'                 => [new Uuid(), [PsalmType::NonEmptyString]],
        ];
        // phpcs:enable
    }

    public function testGetTypesFiltersExisting(): void
    {
        $visitor = new StringValidatorVisitor();
        $actual  = $visitor->getTypes(new Barcode(), [PsalmType::Bool]);
        self::assertSame([], $actual);
    }

    public function testGetTypesUsesConstructorValidators(): void
    {
        $expected = [PsalmType::Bool];
        $visitor  = new StringValidatorVisitor([Uuid::class]);
        $actual   = $visitor->getTypes(new Barcode(), $expected);
        self::assertSame($expected, $actual);
    }
}
