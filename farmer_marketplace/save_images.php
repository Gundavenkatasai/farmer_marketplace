<?php

// Function to create directory if it doesn't exist
function createDirIfNotExists($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Create necessary directories
$directories = [
    'images/fertilizers',
    'images/seeds',
    'images/organic',
    'images/misc'
];

foreach ($directories as $dir) {
    createDirIfNotExists($dir);
}

// Save the images
$images = [
    [
        'path' => 'images/fertilizers/dr-earth-fertilizer.jpg',
        'data' => 'YOUR_BASE64_STRING_HERE'
    ],
    [
        'path' => 'images/fertilizers/npk-fertilizer.jpg',
        'data' => 'YOUR_BASE64_STRING_HERE'
    ],
    [
        'path' => 'images/organic/growise-compost.jpg',
        'data' => 'YOUR_BASE64_STRING_HERE'
    ],
    [
        'path' => 'images/seeds/tomato-seeds.jpg',
        'data' => 'YOUR_BASE64_STRING_HERE'
    ],
    [
        'path' => 'images/misc/seeds-hand.jpg',
        'data' => 'YOUR_BASE64_STRING_HERE'
    ]
];

foreach ($images as $image) {
    $data = base64_decode(str_replace('data:image/jpeg;base64,', '', $image['data']));
    file_put_contents($image['path'], $data);
}

echo "Images saved successfully!\n";
?> 