<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-import-type FormCliConfigurationArray from ConfigProvider
 * @psalm-import-type ReplaceTuple from RegexPattern
 * @psalm-type RegexPatternArray = array{pattern: string, types: list<PsalmType>, replace: list<ReplaceTuple>}
 */
final readonly class RegexVisitorFactory
{
    public function __invoke(ContainerInterface $container): RegexVisitor
    {
        /** @var FormCliConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var list<RegexPatternArray> $regexPatterns */
        $regexPatterns = $config['laminas-form-cli']['array-shape']['validator']['regex']['patterns'] ?? [];
        $patterns      = array_map(
            static fn (array $pattern): RegexPattern => new RegexPattern(...$pattern),
            $regexPatterns
        );
        return new RegexVisitor(...$patterns);
    }
}
