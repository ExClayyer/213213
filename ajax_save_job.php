<?php
require_once 'config.php';

if (!auth()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$job_id = $_POST['job_id'] ?? 0;

// Проверяем, сохранена ли уже вакансия
$stmt = $DB->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
$stmt->execute([$_SESSION['user']['id'], $job_id]);

if ($stmt->fetch()) {
    // Удаляем из сохраненных
    $stmt = $DB->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$_SESSION['user']['id'], $job_id]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Добавляем в сохраненные
    $stmt = $DB->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user']['id'], $job_id]);
    echo json_encode(['success' => true, 'action' => 'saved']);
}
?>