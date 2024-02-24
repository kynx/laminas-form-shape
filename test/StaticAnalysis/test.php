<?php

declare(strict_types=1);

use KynxTest\Laminas\FormShape\StaticAnalysis\Artist;

require 'vendor/autoload.php';

$data = [
    'name' => 'Mr NoAlbums',
];

$artist = new Artist();
$artist->setData($data);
assert($artist->isValid());

$formData = $artist->getData();
assert(is_array($formData));
assert($formData['id'] === null);
assert($formData['name'] !== null);
assert($formData['albums'] === null);

