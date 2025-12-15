<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Composer\InstalledVersions;
use Exception;
use OutOfBoundsException;
use Psalm\Config;

use function define;
use function defined;
use function getcwd;

final class ConfigLoader
{
    public const DEFAULT_STRING_LENGTH = 1000;

    private static bool $loaded = false;

    public static function load(?int $maxStringLength = null): void
    {
        if (self::$loaded) {
            $config = Config::getInstance();
        } else {
            self::hackPsalmCli();
            self::setPsalmVersion();

            $config       = Config::getConfigForPath(__DIR__, (string) getcwd());
            self::$loaded = true;
        }

        if ($maxStringLength !== null) {
            $config->max_string_length = $maxStringLength;
        }
    }

    /**
     * Prevent Psalm's `CliUtils::getRawCliArguments()` from trying to parse script arguments
     */
    private static function hackPsalmCli(): void
    {
        // phpcs:ignore Squiz.PHP.GlobalKeyword.NotAllowed
        global $argv;
        $argv = [];
    }

    private static function setPsalmVersion(): void
    {
        if (defined('PSALM_VERSION')) {
            return;
        }

        $packageName = 'vimeo/psalm';
        try {
            $version = (string) InstalledVersions::getPrettyVersion($packageName)
                . '@' . (string) InstalledVersions::getReference($packageName);
        } catch (OutOfBoundsException $e) {
            throw new Exception("Cannot get Psalm version", 0, $e);
        }

        define('PSALM_VERSION', $version);
    }
}
