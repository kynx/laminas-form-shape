<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

use function array_filter;
use function is_a;
use function is_string;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class RegexVisitorFactory
{
    private const VALID_TYPES = [TFloat::class, TInt::class, TString::class];

    public function __invoke(ContainerInterface $container): RegexVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var array<string, list<class-string<Atomic>>> $regexPatterns */
        $regexPatterns = $config['laminas-form-shape']['validator']['regex']['patterns'] ?? [];

        return new RegexVisitor($this->getPatterns($regexPatterns));
    }

    /**
     * @param array<string, list<class-string<Atomic>>> $patterns
     * @return array<string, Union>
     */
    private function getPatterns(array $patterns): array
    {
        $regex = [];
        foreach ($patterns as $pattern => $narrow) {
            $types = [];
            foreach ($narrow as $classString) {
                $this->assertIsTString($classString);
                /** @psalm-suppress UnsafeInstantiation */
                $types[$pattern] = new $classString();
            }
            if ($types !== []) {
                $regex[$pattern] = new Union($types);
            }
        }

        return $regex;
    }

    /**
     * @psalm-assert class-string<TFloat|TInt|TString> $classString
     */
    private function assertIsTString(mixed $classString): void
    {
        if (! is_string($classString)) {
            throw InvalidValidatorConfigurationException::forRegex($classString);
        }
        $valid = (bool) array_filter(
            self::VALID_TYPES,
            static fn (string $type): bool => is_a($classString, $type, true)
        );
        if (! $valid) {
            throw InvalidValidatorConfigurationException::forRegex($classString);
        }
    }
}
