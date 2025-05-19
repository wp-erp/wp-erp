<?php

/**
 * This script patches the return type of specific methods in the Eloquent Model and Collection classes
 * to avoid deprecation warnings in PHP 8.*.
 *
 * It adds the #[ReturnTypeWillChange] attribute to methods that are missing a return type declaration.
 */
function patchReturnType($file, $method) {
    $content = file_get_contents($file);

    // Improved check to ensure attribute is not already present
    $pattern = "/#[ \t]*\\\\?ReturnTypeWillChange[ \t]*\n[ \t]*public function[ \t]+" . preg_quote($method) . "[ \t]*\(/";
    if (preg_match($pattern, $content)) {
        echo "⚠️ Already patched: $method\n";
        return;
    }

    // Now patch the method
    $patchPattern = "/(public function[ \t]+" . preg_quote($method) . "[ \t]*\([^)]*\))/";
    $replacement = "#[\\ReturnTypeWillChange]\n    $1";

    $patched = preg_replace($patchPattern, $replacement, $content, 1, $count);

    if ($count > 0) {
        file_put_contents($file, $patched);
        echo "✅ Patched: $method\n";
    } else {
        echo "❌ Could not patch: $method\n";
    }
}

// Example usage
$targetModelFile = __DIR__ . '/../vendor/illuminate/database/Eloquent/Model.php';
$targetCollectionFile = __DIR__ . '/../vendor/illuminate/support/Collection.php';

$methods = ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset', 'jsonSerialize', 'count', 'getIterator'];

foreach ($methods as $method) {
    patchReturnType($targetModelFile, $method);
    patchReturnType($targetCollectionFile, $method);
}
