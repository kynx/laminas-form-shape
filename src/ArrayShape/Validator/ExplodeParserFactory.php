<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-import-type ConfigProviderArray from ConfigProvider
 */
final readonly class ExplodeParserFactory
{
    public function __invoke(ContainerInterface $container): ExplodeParser
    {
        /** @var ConfigProviderArray $config */
        $config = $container->get('config') ?? [];
        /** @var array{item-types?: list<PsalmType>} $parserConfig */
        $parserConfig = (array) ($config['laminas-form-cli']['array-shape']['validator']['explode'] ?? []);
        $itemTypes    = $parserConfig['item-types'] ?? ExplodeParser::DEFAULT_ITEM_TYPES;

        $validatorParsers = [];
        foreach ($config['laminas-form-cli']['array-shape']['validator-parsers'] ?? [] as $parserName) {
            $validatorParsers[] = $this->getParser($container, $parserName);
        }

        return new ExplodeParser($validatorParsers, $itemTypes);
    }

    /**
     * @param class-string<ValidatorParserInterface> $parserName
     */
    private function getParser(ContainerInterface $container, string $parserName): ValidatorParserInterface
    {
        if ($container->has($parserName)) {
            $parser = $container->get($parserName);
        } else {
            $parser = new $parserName();
        }

        assert($parser instanceof ValidatorParserInterface);
        return $parser;
    }
}
