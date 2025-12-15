<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\CallbackVisitor;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\FileInputStyle;
use Kynx\Laminas\FormShape\InputFilter\FileInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitor;
use Kynx\Laminas\FormShape\Validator\StringLengthVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\Callback;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Http\Message\UploadedFileInterface;

#[CoversClass(FileInputVisitor::class)]
final class FileInputVisitorTest extends TestCase
{
    public function testVisitNonFileInputReturnsNull(): void
    {
        $input   = self::createStub(InputInterface::class);
        $visitor = new FileInputVisitor(FileInputStyle::Both, [], []);

        $actual = $visitor->visit($input);
        self::assertNull($actual);
    }

    /**
     * @param non-empty-array<Atomic> $expected
     */
    #[DataProvider('styleProvider')]
    public function testVisitReturnsInitialUnion(FileInputStyle $style, array $expected): void
    {
        $expected = new Union($expected);
        $input    = new FileInput('foo');
        $visitor  = new FileInputVisitor($style, [], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public static function styleProvider(): array
    {
        return [
            'laminas' => [
                FileInputStyle::Laminas,
                [
                    new TNull(),
                    new TString(),
                    FileValidatorVisitor::getUploadArray(),
                ],
            ],
            'psr-7'   => [
                FileInputStyle::Psr7,
                [
                    new TNull(),
                    new TString(),
                    new TNamedObject(UploadedFileInterface::class),
                ],
            ],
            'both'    => [
                FileInputStyle::Both,
                [
                    new TNull(),
                    new TString(),
                    FileValidatorVisitor::getUploadArray(),
                    new TNamedObject(UploadedFileInterface::class),
                ],
            ],
        ];
    }

    public function testVisitDoesNotPrependUploadFileValidator(): void
    {
        $mockVisitor = self::createMock(ValidatorVisitorInterface::class);
        $mockVisitor->expects(self::never())
            ->method('visit');
        $visitor = new FileInputVisitor(FileInputStyle::Both, [], [$mockVisitor]);
        $input   = new FileInput('foo');
        $input->setAutoPrependUploadValidator(false);

        $actual = $visitor->visit($input);
        self::assertInstanceOf(Union::class, $actual);
    }

    public function testVisitDoesNotPrependDuplicateUploadFileValidator(): void
    {
        $expected    = new Union(self::getInitialTypes());
        $mockVisitor = self::createStub(ValidatorVisitorInterface::class);
        $mockVisitor->method('visit')
            ->willReturnCallback(
                static function (ValidatorInterface $validator, Union $previous) use ($expected): Union {
                    self::assertEquals($expected, $previous);
                    return $previous;
                }
            );
        $visitor = new FileInputVisitor(FileInputStyle::Psr7, [], [$mockVisitor, new FileValidatorVisitor()]);
        $input   = new FileInput('foo');
        $input->getValidatorChain()->attach(new UploadFile());

        $actual = $visitor->visit($input);
        self::assertInstanceOf(Union::class, $actual);
    }

    public function testVisitPrependsUploadFileValidator(): void
    {
        $expected = new Union([new TNamedObject(UploadedFileInterface::class)]);
        $visitor  = new FileInputVisitor(FileInputStyle::Psr7, [], [new FileValidatorVisitor()]);
        $input    = new FileInput('foo');

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param non-empty-array<Atomic> $expected
     */
    #[DataProvider('widenTypeProvider')]
    public function testVisitWidensType(bool $continueIfEmpty, bool $allowEmpty, bool $required, array $expected): void
    {
        $expected = new Union($expected);
        $visitor  = new FileInputVisitor(FileInputStyle::Psr7, [], [new FileValidatorVisitor()]);
        $input    = new FileInput('foo');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public static function widenTypeProvider(): array
    {
        $validated = [new TNamedObject(UploadedFileInterface::class)];

        return [
            "continue, allow, required"                 => [true, true, true, self::getInitialTypes()],
            "continue, allow, not required"             => [true, true, false, self::getInitialTypes()],
            "continue, don't allow, required"           => [true, false, true, self::getInitialTypes()],
            "continue, don't allow, not required"       => [true, false, false, self::getInitialTypes()],
            "don't continue, allow, required"           => [false, true, true, self::getInitialTypes()],
            "don't continue, allow, not required"       => [false, true, false, self::getInitialTypes()],
            "don't continue, don't allow, required"     => [false, false, true, $validated],
            "don't continue, don't allow, not required" => [false, false, false, self::getInitialTypes()],
        ];
    }

    public function testVisitConvertsCallableToCallbackFilter(): void
    {
        $expected = new Union([new TString()]);
        $callable = static fn (): string => 'test';
        $input    = new FileInput('foo');
        $input->getFilterChain()->attach($callable);
        $visitor = new FileInputVisitor(FileInputStyle::Psr7, [new CallbackVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitValidatesThenFilters(): void
    {
        $expected      = new Union([new TString()]);
        $filterVisitor = self::createStub(FilterVisitorInterface::class);
        $filterVisitor->method('visit')
            ->willReturnCallback(static function (FilterInterface $filter, Union $previous): Union {
                $expected = new Union([new TNamedObject(UploadedFileInterface::class)]);
                self::assertEquals($expected, $previous);
                return new Union([new TString()]);
            });
        $filter = new Callback(static fn (): string => 'test');
        $input  = new FileInput('foo');
        $input->getFilterChain()->attach($filter);
        $visitor = new FileInputVisitor(FileInputStyle::Psr7, [$filterVisitor], [new FileValidatorVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitAddsFallbackValue(): void
    {
        $expected = new Union([new TNamedObject(UploadedFileInterface::class), new TLiteralInt(123)]);
        $input    = new FileInput('foo');
        $input->setFallbackValue(123);
        $visitor = new FileInputVisitor(FileInputStyle::Psr7, [], [new FileValidatorVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitEmptyUnionThrowsException(): void
    {
        $input = new FileInput('foo');
        $input->getValidatorChain()->attach(new StringLength());
        $visitor = new FileInputVisitor(FileInputStyle::Psr7, [], [
            new FileValidatorVisitor(),
            new StringLengthVisitor(),
        ]);

        self::expectException(InputVisitorException::class);
        self::expectExceptionMessage("Cannot get type");
        $visitor->visit($input);
    }

    /**
     * @return non-empty-array<Atomic>
     */
    private static function getInitialTypes(): array
    {
        return [
            new TNull(),
            new TString(),
            new TNamedObject(UploadedFileInterface::class),
        ];
    }
}
