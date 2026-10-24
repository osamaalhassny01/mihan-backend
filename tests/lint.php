<?php
$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$errors = [];
$total = 0;

echo "Linting all PHP files in: $root\n";

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();

    // Skip vendor or backup files if any
    if (strpos($path, 'mihan_backup') !== false) continue;

    $total++;
    $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
    $output = shell_exec($cmd);
    if (strpos($output, 'No syntax errors detected') === false) {
        $errors[$path] = trim($output);
        echo " [X] $path\n     $output\n";
    }
}

echo "----------------------------------------\n";
echo "Scanned $total PHP files.\n";

if (empty($errors)) {
    echo "SUCCESS: All $total PHP files passed php -l linting without errors!\n";
    exit(0);
} else {
    echo "FAILURE: " . count($errors) . " files have syntax errors.\n";
    exit(1);
}
