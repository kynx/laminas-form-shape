<?php

declare(strict_types=1);

use KynxTest\Laminas\FormShape\StaticAnalysis\Artist;

require 'vendor/autoload.php';

$nigel = new Artist();
$data  = [
    'name' => 'Nigel NoAlbums',
];
$nigel->setData($data);
assert($nigel->isValid());

$formData = $nigel->getData();
assert(is_array($formData));
assert($formData['id'] === null);
assert($formData['name'] !== null);

$albums = $formData['albums'];
assert(! isset($albums[0]));

$wendy = new Artist();
$data  = [
    'id'     => '123',
    'name'   => 'Wendy OneAlbum',
    'albums' => [
        [
            'id'     => '',
            'title'  => 'Woe is Wendy',
            'genre'  => '3',
            'rating' => '10',
        ],
    ],
];
$wendy->setData($data);
$isValid = $wendy->isValid();
assert($isValid);

$formData = $wendy->getData();
assert(is_array($formData));
assert($formData['id'] !== null);

$albums = $formData['albums'];
assert(isset($albums[0]));
assert($albums[0]['genre'] === '3');
