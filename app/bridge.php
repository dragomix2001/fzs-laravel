<?php

// Bridge classes to maintain backward compatibility
// These aliases allow old code using App\ModelName to work with App\Models\ModelName

$modelsPath = __DIR__ . '/app/Models';
$modelFiles = glob($modelsPath . '/*.php');

foreach ($modelFiles as $file) {
    $filename = basename($file, '.php');
    
    // Skip abstract classes and base classes
    if (in_array($filename, ['AndroModel'])) {
        continue;
    }
    
    // Create class alias if class doesn't exist in App\ namespace
    $oldClass = 'App\\' . $filename;
    $newClass = 'App\\Models\\' . $filename;
    
    if (class_exists($newClass) && !class_exists($oldClass)) {
        class_alias($newClass, $oldClass);
    }
}
