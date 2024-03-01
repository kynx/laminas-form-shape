<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormShape\Filter\CallbackVisitor;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Laminas\Filter\Callback;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TString;

#[CoversClass(CallbackVisitor::class)]
final class CallbackVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        /** @psalm-suppress MissingClosureReturnType */
        $noReturn     = static fn () => 123;
        $closure      = static fn (): int => 123;
        $union        = static fn (): DateTime|DateTimeImmutable => new DateTimeImmutable('now');
        $intersection = static fn (): FilterVisitorInterface&Stub => self::createStub(FilterVisitorInterface::class);
        $invokable    = new class () {
            public function __invoke(): int
            {
                return 123;
            }
        };
        $callable     = new class () {
            public function filter(): int
            {
                return 123;
            }
        };
        $self         = new class () {
            public function __invoke(): self
            {
                return $this;
            }
        };
        $static       = new class () {
            public function __invoke(): static
            {
                return $this;
            }
        };
        $parent       = new class () extends DateTimeImmutable {
            public function __invoke(): parent
            {
                return new DateTimeImmutable();
            }
        };

        return [
            'no return'    => [new Callback($noReturn), [new TString()], [new TString()]],
            'closure'      => [new Callback($closure), [new TString()], [new TInt()]],
            'invokable'    => [new Callback($invokable), [new TString()], [new TInt()]],
            'array'        => [new Callback([$callable, 'filter']), [new TString()], [new TInt()]],
            'string'       => [new Callback('intval'), [new TString()], [new TInt()]],
            'self'         => [new Callback($self), [new TString()], [new TNamedObject($self::class)]],
            'static'       => [new Callback($static), [new TString()], [new TNamedObject($static::class)]],
            'parent'       => [
                new Callback($parent),
                [new TString()],
                [new TNamedObject(DateTimeImmutable::class)],
            ],
            'union'        => [
                new Callback($union),
                [new TString()],
                [new TNamedObject(DateTime::class), new TNamedObject(DateTimeImmutable::class)],
            ],
            'intersection' => [
                new Callback($intersection),
                [new TString()],
                [
                    new TNamedObject(
                        FilterVisitorInterface::class,
                        false,
                        false,
                        [Stub::class => new TNamedObject(Stub::class)]
                    ),
                ],
            ],
        ];
    }

    protected function getVisitor(): FilterVisitorInterface
    {
        return new CallbackVisitor();
    }
}
