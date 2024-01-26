<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-import-type ConfigProviderArray from ConfigProvider
 */
final readonly class InputParserFactory
{
    public function __invoke(ContainerInterface $container): InputParser
    {
        /** @var ConfigProviderArray $config */
        $config = $container->get('config') ?? [];

        $filterParsers = [];
        foreach ($config['laminas-form-cli']['array-shape']['filter-parsers'] as $parserName) {
            $parser = $this->getParser($container, $parserName);
            assert($parser instanceof FilterParserInterface);
            $filterParsers[] = $parser;
        }

        $validatorParsers = [];
        foreach ($config['laminas-form-cli']['array-shape']['validator-parsers'] as $parserName) {
            $parser = $this->getParser($container, $parserName);
            assert($parser instanceof ValidatorParserInterface);
            $validatorParsers[] = $parser;
        }

        return new InputParser($filterParsers, $validatorParsers);
    }

    /**
     * @param class-string<FilterParserInterface|ValidatorParserInterface> $parserName
     */
    private function getParser(
        ContainerInterface $container,
        string $parserName
    ): FilterParserInterface|ValidatorParserInterface {
        if ($container->has($parserName)) {
            return $container->get($parserName);
        }

        return new $parserName();
    }
}
