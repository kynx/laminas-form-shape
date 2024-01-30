<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 * @psalm-import-type ReplaceTuple from RegexPattern
 * @psalm-type RegexPatternArray = array{pattern: string, types: list<PsalmType>, replace: list<ReplaceTuple>}
 */
final readonly class RegexVisitorFactory
{
    public function __invoke(ContainerInterface $container): RegexVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var list<RegexPatternArray> $regexPatterns */
        $regexPatterns = $config['laminas-form-shape']['validator']['regex']['patterns'] ?? [];
        $patterns      = array_map(
            static fn (array $pattern): RegexPattern => new RegexPattern(...$pattern),
            $regexPatterns
        );
        return new RegexVisitor(...$patterns);
    }
}
