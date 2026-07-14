<?php
$base = __DIR__ . '/../';
$used = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $path = $file->getPathname();
    if (stripos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false || stripos($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false || stripos($path, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = ['php', 'blade.php', 'js', 'ts', 'vue', 'json', 'md', 'txt', 'yml', 'yaml'];
    if (!in_array($ext, $allowed, true)) {
        continue;
    }
    $content = @file_get_contents($path);
    if ($content === false) {
        continue;
    }
        if (preg_match_all('/heroicon-[os]-[a-z0-9-]+/i', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $used[strtolower($match)][] = $path;
            }
    }
}
$svgDir = $base . 'vendor' . DIRECTORY_SEPARATOR . 'blade-ui-kit' . DIRECTORY_SEPARATOR . 'blade-heroicons' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'svg';
$avail = [];
foreach (new DirectoryIterator($svgDir) as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $filename = $file->getBasename('.svg');
    $avail['heroicon-' . strtolower($filename)] = true;
}
$invalid = [];
foreach (array_keys($used) as $name) {
    if (!isset($avail[$name])) {
        $invalid[$name] = array_unique($used[$name]);
    }
}
sort($invalid);
foreach ($invalid as $name => $paths) {
    echo $name, "\n";
    foreach ($paths as $path) {
        echo "  $path\n";
    }
}
if (empty($invalid)) {
    echo "NONE\n";
}
