<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\UseCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TAnonymousClassInstance;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

#[CoversClass(UseCollector::class)]
final class UseCollectorTest extends TestCase
{
    #[DataProvider('enterNodeProvider')]
    public function testEnterNode(Atomic $type, array $expected): void
    {
        $union     = new Union([$type]);
        $collector = new UseCollector();

        $collector->traverse($union);
        $actual = $collector->getUses();
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{Atomic, list<string>}>
     */
    public static function enterNodeProvider(): array
    {
        return [
            'TTypeAlias'              => [new TTypeAlias(self::class, 'TFoo'), [self::class]],
            'TClosure'                => [new TClosure(), []],
            'TAnonymousClassInstance' => [new TAnonymousClassInstance('class {}'), []],
            'TNamedObject'            => [new TNamedObject(self::class), [self::class]],
        ];
    }
}
