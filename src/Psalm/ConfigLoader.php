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
    private static bool $loaded = false;

    public static function load(int $maxStringLength): void
    {
        if (self::$loaded) {
            return;
        }

        self::hackPsalmCli();
        self::setPsalmVersion();

        $config                    = Config::getConfigForPath(__DIR__, getcwd());
        $config->max_string_length = $maxStringLength;

        self::$loaded = true;
    }

    /**
     * Prevent psalm's `CliUtils` from trying to parse script arguments
     */
    private static function hackPsalmCli(): void
    {
        // phpcs:disable Squiz.PHP.GlobalKeyword.NotAllowed
        global $argv;
        $argv = [];
        // phpcs:enable
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
