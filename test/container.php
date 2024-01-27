<?php

declare(strict_types=1);

use Kynx\Laminas\FormCli\ConfigProvider;
use Laminas\ServiceManager\ServiceManager;

$config = (new ConfigProvider())();

$dependencies = $config['dependencies'];
/** @psalm-suppress MixedArrayAssignment */
$dependencies['services']['config'] = $config;

/** @psalm-suppress MixedArgumentTypeCoercion */
return new ServiceManager($dependencies);
