<?php
require_once 'config.php';

$term = $_GET['term'] ?? '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$suggestions = [];

// Ищем вакансии
$stmt = $DB->prepare("
    SELECT DISTINCT title as value, 'job' as type 
    FROM jobs 
    WHERE status = 'active' AND expiry_date >= CURDATE() 
    AND title LIKE ? 
    LIMIT 5
");
$stmt->execute(["%$term%"]);
$suggestions = array_merge($suggestions, $stmt->fetchAll());

// Ищем компании
$stmt = $DB->prepare("
    SELECT DISTINCT name as value, 'company' as type 
    FROM companies 
    WHERE verified = 1 AND name LIKE ? 
    LIMIT 5
");
$stmt->execute(["%$term%"]);
$suggestions = array_merge($suggestions, $stmt->fetchAll());

// Ищем категории
$stmt = $DB->prepare("
    SELECT DISTINCT name as value, 'category' as type 
    FROM job_categories 
    WHERE name LIKE ? 
    LIMIT 5
");
$stmt->execute(["%$term%"]);
$suggestions = array_merge($suggestions, $stmt->fetchAll());

echo json_encode($suggestions);
?>