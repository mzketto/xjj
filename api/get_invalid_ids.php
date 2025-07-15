<?php
header('Content-Type: application/json');

$filename = __DIR__ . 'invalid_ids.txt';

$invalidEntries = [];
if (file_exists($filename)) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 格式：apiName id
        $parts = explode(' ', $line, 2);
        if (count($parts) === 2 && is_numeric($parts[1])) {
            $invalidEntries[] = [
                'api_name' => $parts[0],
                'id' => intval($parts[1])
            ];
        }
    }
}

echo json_encode([
    'code' => 1,
    'invalid_entries' => $invalidEntries,
]);
