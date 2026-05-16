<?php
/**
 * Simple API endpoint to fetch sections by class ID
 */
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');

if (!isset($_GET['class_id'])) {
    echo json_encode([]);
    exit;
}

$classId = (int)$_GET['class_id'];

$sections = Database::fetchAll(
    "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name",
    [$classId]
);

echo json_encode($sections);
