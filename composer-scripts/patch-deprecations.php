<?php

/**
 * This script patches the return type of specific methods in the Eloquent Model and Collection classes
 * to avoid deprecation warnings in PHP 8.*.
 *
 * It adds the #[ReturnTypeWillChange] attribute to methods that are missing a return type declaration.
 */
function patchReturnType($file, $method) {
    $content = file_get_contents($file);

    // Check if method is already patched with the attribute
    $alreadyPatched = preg_match(
        '/#[ \t]*\\\\?ReturnTypeWillChange[ \t]*\n[ \t]*public function[ \t]+' . preg_quote($method) . '\s*\(/',
        $content
    );

    // Alternatively, scan just before the method for attribute presence
    if (! $alreadyPatched) {
        // Match the method declaration with optional preceding attributes
        $methodPattern = '/((?:\s*#\[.*?\]\s*)*)\s*(public function[ \t]+' . preg_quote($method) . '\s*\([^)]*\))/';

        $patched = preg_replace_callback($methodPattern, function($matches) use ($method) {
            $attributes = $matches[1];
            $signature  = $matches[2];

            // Avoid duplicating ReturnTypeWillChange
            if (stripos($attributes, 'ReturnTypeWillChange') !== false) {
                echo "⚠️ Already patched: $method\n";
                return $matches[0];
            }

            echo "✅ Patched: $method\n";
            return "#[\\ReturnTypeWillChange]\n    " . $signature;
        }, $content, 1, $count);

        if ($count > 0) {
            file_put_contents($file, $patched);
        } else {
            echo "❌ Could not patch: $method\n";
        }
    } else {
        echo "⚠️ Already patched: $method\n";
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
