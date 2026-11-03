<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = view('welcome')->render();

// Normalisasi asset path menjadi relative path untuk static hosting
$html = str_replace(rtrim(asset('images'), '/') . '/', 'images/', $html);
$html = preg_replace('#https?://[^/]+/images/#', 'images/', $html);
$html = str_replace('images//', 'images/', $html);

// Buat direktori landing-vercel dan images jika belum ada
$outputDir = __DIR__ . '/../landing-vercel';
$imagesDir = $outputDir . '/images';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

// Tulis index.html
file_put_contents($outputDir . '/index.html', $html);

// Salin gambar yang diperlukan
$images = [
    'iconfav.png',
    'uji.jpeg',
    'tbm.jpeg',
    'umbulan.jpg',
    'pdam.webp',
    'booster.jpeg',
];

$sourceImagesDir = __DIR__ . '/../public/images';
foreach ($images as $img) {
    if (file_exists($sourceImagesDir . '/' . $img)) {
        copy($sourceImagesDir . '/' . $img, $imagesDir . '/' . $img);
    }
}

echo "EXPORT_SUCCESS: " . strlen($html) . " bytes\n";
