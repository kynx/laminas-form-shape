#!/usr/bin/env php
<?php

declare(strict_types=1);

$command = array_shift($argv);
echo basename($command) . ' ' . implode(' ', (array) $argv);
