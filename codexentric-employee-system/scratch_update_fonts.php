<?php
$directory = new RecursiveDirectoryIterator(__DIR__);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.(php|css)$/i', RecursiveRegexIterator::GET_MATCH);

$fontUrlPattern = '/https:\/\/fonts\.googleapis\.com\/css2\?family=[^"\'&]+(&[^"\']+)?/i';
$newFontUrl = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap';

$fontFamilyPatterns = [
    "/'Nunito',\s*sans-serif/i",
    "/'Source Sans 3',\s*sans-serif/i",
    "/-apple-system,\s*BlinkMacSystemFont,\s*'Segoe UI',\s*'Roboto',\s*'Oxygen',\s*'Ubuntu',\s*'Cantarell',\s*'Fira Sans',\s*'Droid Sans',\s*'Helvetica Neue',\s*sans-serif/i",
    "/'Inter',\s*system-ui,\s*-apple-system,\s*sans-serif/i"
];
$newFontFamily = "'Inter', sans-serif";

foreach ($regex as $file) {
    $filePath = $file[0];
    
    // skip scratch files and node_modules if any
    if (strpos($filePath, 'scratch_') !== false) continue;
    if (strpos($filePath, 'node_modules') !== false) continue;
    
    $content = file_get_contents($filePath);
    $original = $content;
    
    // Update font URL
    $content = preg_replace($fontUrlPattern, $newFontUrl, $content);
    
    // Update font families
    foreach ($fontFamilyPatterns as $pattern) {
        $content = preg_replace($pattern, $newFontFamily, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Updated fonts in: " . str_replace(__DIR__, '', $filePath) . "\n";
    }
}
echo "Done.\n";
